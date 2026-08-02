<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DynamicReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $collection;
    protected $headings;
    protected $mappingCallback;
    protected $totalColumns;

    public function __construct($collection, array $headings, callable $mappingCallback, array $totalColumns = [])
    {
        $this->collection = $collection;
        $this->headings = $headings;
        $this->mappingCallback = $mappingCallback;
        $this->totalColumns = $totalColumns;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        return call_user_func($this->mappingCallback, $row);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '4F46E5']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Habilitar Autofiltro en la tabla de datos
                $sheet->setAutoFilter('A1:' . $highestColumn . $highestRow);

                // Agregar fila de totales dinámicos
                if (!empty($this->totalColumns) && $highestRow > 1) {
                    $totalRow = $highestRow + 1;
                    $sheet->setCellValue('A' . $totalRow, 'TOTALES');
                    $sheet->getStyle('A' . $totalRow . ':' . $highestColumn . $totalRow)->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'F3F4F6']]
                    ]);

                    foreach ($this->totalColumns as $colIndex) {
                        // El índice es 0-based, PhpSpreadsheet usa 1-based para las columnas
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                        
                        // Fórmula SUBTOTAL 109 ignora filas ocultas (por filtro)
                        $formula = "=SUBTOTAL(109, {$colLetter}2:{$colLetter}{$highestRow})";
                        $sheet->setCellValue($colLetter . $totalRow, $formula);
                    }
                }
            }
        ];
    }
}
