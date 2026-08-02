<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Get;
use App\Models\Category;

class ReportGenerator extends Page implements HasForms
{
    use InteractsWithForms;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-document-text';
    }

    public static function getNavigationLabel(): string
    {
        return 'Reportes';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Reportes';
    }

    public static function getNavigationSort(): ?int
    {
        return 10;
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Generador de Reportes Dinámicos';
    }

    protected string $view = 'filament.admin.pages.report-generator';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Configuración del Reporte')->schema([
                    Select::make('report_type')
                        ->label('Tipo de Reporte')
                        ->options([
                            'sales' => 'Ventas por Rango de Fecha',
                            'inventory_valuation' => 'Valoración de Inventario',
                            'low_stock' => 'Productos con Bajo Stock',
                        ])
                        ->required()
                        ->live(),

                    DatePicker::make('date_start')
                        ->label('Fecha Inicial')
                        ->visible(fn (Get $get) => $get('report_type') === 'sales')
                        ->required(fn (Get $get) => $get('report_type') === 'sales'),

                    DatePicker::make('date_end')
                        ->label('Fecha Final')
                        ->visible(fn (Get $get) => $get('report_type') === 'sales')
                        ->required(fn (Get $get) => $get('report_type') === 'sales'),

                    Select::make('category_id')
                        ->label('Filtrar por Categoría (Opcional)')
                        ->options(Category::pluck('name', 'id'))
                        ->searchable()
                        ->visible(fn (Get $get) => in_array($get('report_type'), ['inventory_valuation', 'low_stock', 'sales'])),

                    Select::make('format')
                        ->label('Formato de Exportación')
                        ->options([
                            'pdf' => 'Documento PDF (.pdf)',
                            'excel' => 'Hoja de Cálculo (.xlsx)',
                        ])
                        ->default('pdf')
                        ->required(),
                        
                    Actions::make([
                        Action::make('submit')
                            ->label('Generar y Descargar Reporte')
                            ->icon('heroicon-o-document-arrow-down')
                            ->color('primary')
                            ->submit('generate')
                    ])->columnSpanFull()->alignEnd(),
                ])->columns(2),
            ])
            ->statePath('data');
    }

    public function generate()
    {
        $data = $this->form->getState();
        $reportType = $data['report_type'];
        $format = $data['format'];

        $query = null;
        $headings = [];
        $mappingCallback = null;
        $totalColumns = [];
        $title = '';
        $subtitle = '';
        $index = 2; // Inicia en 2 porque la fila 1 son los encabezados en Excel

        if ($reportType === 'sales') {
            $title = 'Reporte de Ventas';
            $subtitle = "Desde: {$data['date_start']} Hasta: {$data['date_end']}";
            
            $query = \App\Models\Sale::query()
                ->whereBetween('created_at', [$data['date_start'] . ' 00:00:00', $data['date_end'] . ' 23:59:59']);

            $headings = ['Ítem', 'ID Venta', 'Cliente', 'Subtotal (USD)', 'IGTF (USD)', 'Total (USD)', 'Total (Bs)', 'Fecha'];
            $totalColumns = [3, 4, 5, 6]; // Subtotal, IGTF, Total USD, Total Bs
            
            $mappingCallback = function($sale) use ($format, &$index) {
                $isExcel = $format === 'excel';
                $currentRow = $index++;
                return [
                    $isExcel ? "=SUBTOTAL(103, B$2:B{$currentRow})" : ($currentRow - 1),
                    $sale->id,
                    $sale->customer_name ?: 'Cliente Final',
                    $isExcel ? (float)($sale->subtotal_usd ?? 0) : '$' . number_format($sale->subtotal_usd ?? 0, 2),
                    $isExcel ? (float)($sale->igtf_usd ?? 0) : '$' . number_format($sale->igtf_usd ?? 0, 2),
                    $isExcel ? (float)($sale->total_usd ?? 0) : '$' . number_format($sale->total_usd ?? 0, 2),
                    $isExcel ? (float)($sale->total_ves ?? 0) : 'Bs ' . number_format($sale->total_ves ?? 0, 2),
                    $sale->created_at ? $sale->created_at->format('d/m/Y H:i') : 'N/A'
                ];
            };
        } elseif ($reportType === 'inventory_valuation' || $reportType === 'low_stock') {
            $query = \App\Models\Product::with('category', 'supplier');
            
            if (!empty($data['category_id'])) {
                $query->where('category_id', $data['category_id']);
                $category = \App\Models\Category::find($data['category_id']);
                $subtitle = $category ? "Categoría: {$category->name}" : "";
            }

            if ($reportType === 'inventory_valuation') {
                $title = 'Valoración de Inventario';
                $headings = ['Ítem', 'SKU', 'Producto', 'Categoría', 'Stock', 'Costo Unit.', 'Valor Total'];
                $totalColumns = [4, 6]; // Stock, Valor Total
                
                $mappingCallback = function($product) use ($format, &$index) {
                    $isExcel = $format === 'excel';
                    $totalValue = $product->stock_quantity * ($product->cost_price_usd ?? 0);
                    $currentRow = $index++;
                    return [
                        $isExcel ? "=SUBTOTAL(103, C$2:C{$currentRow})" : ($currentRow - 1),
                        $product->sku ?? 'N/A',
                        $product->name,
                        $product->category ? $product->category->name : 'N/A',
                        $product->stock_quantity,
                        $isExcel ? (float)($product->cost_price_usd ?? 0) : '$' . number_format($product->cost_price_usd ?? 0, 2),
                        $isExcel ? (float)$totalValue : '$' . number_format($totalValue, 2),
                    ];
                };
            } else {
                $title = 'Productos con Bajo Stock';
                $headings = ['Ítem', 'SKU', 'Producto', 'Stock Actual', 'Mínimo Permitido', 'Proveedor'];
                $totalColumns = [3]; // Stock Actual
                $query->whereColumn('stock_quantity', '<=', 'min_stock_alert');
                
                $mappingCallback = function($product) use ($format, &$index) {
                    $isExcel = $format === 'excel';
                    $currentRow = $index++;
                    return [
                        $isExcel ? "=SUBTOTAL(103, C$2:C{$currentRow})" : ($currentRow - 1),
                        $product->sku ?? 'N/A',
                        $product->name,
                        $product->stock_quantity,
                        $product->min_stock_alert,
                        $product->supplier ? $product->supplier->name : 'N/A',
                    ];
                };
            }
        }

        $results = $query->get();

        if ($format === 'excel') {
            $export = new \App\Exports\DynamicReportExport($results, $headings, $mappingCallback, $totalColumns);
            $filename = str_replace(' ', '_', strtolower($title)) . '_' . time() . '.xlsx';
            return \Maatwebsite\Excel\Facades\Excel::download($export, $filename);
        } else {
            $mappedData = $results->map($mappingCallback)->toArray();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.dynamic-pdf', [
                'title' => $title,
                'subtitle' => $subtitle,
                'headings' => $headings,
                'data' => $mappedData
            ]);
            
            $filename = str_replace(' ', '_', strtolower($title)) . '_' . time() . '.pdf';
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename);
        }
    }
}
