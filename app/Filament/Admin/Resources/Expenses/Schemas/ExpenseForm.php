<?php

namespace App\Filament\Admin\Resources\Expenses\Schemas;

use App\Models\ExchangeRate;
use App\Models\Expense;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('registered_by')
                    ->default(fn () => Auth::id()),
                    
                Hidden::make('exchange_rate_id')
                    ->default(fn () => ExchangeRate::latest('created_at')->value('id')),
                    
                Section::make('Detalles del Gasto')
                    ->description('Información principal del egreso')
                    ->schema([
                        Select::make('category')
                            ->label('Categoría')
                            ->options(Expense::$categoryLabels)
                            ->required()
                            ->searchable()
                            ->live(),
                            
                        Select::make('supplier_id')
                            ->label('Proveedor')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => $get('category') === 'supplier'),
                            
                        TextInput::make('description')
                            ->label('Descripción')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                            
                        DatePicker::make('expense_date')
                            ->label('Fecha del Gasto')
                            ->default(now())
                            ->required(),
                            
                        Toggle::make('is_recurring')
                            ->label('¿Es un gasto recurrente?')
                            ->default(false),
                    ])->columns(2),
                    
                Section::make('Montos y Comprobantes')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('amount_usd')
                                ->label('Monto (USD)')
                                ->required()
                                ->numeric()
                                ->prefix('$')
                                ->default(0.00)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, $set) {
                                    $rate = \App\Models\ExchangeRate::latest('created_at')->value('rate') ?? 1;
                                    $set('amount_ves', round((float) $state * $rate, 2));
                                }),
                                
                            TextInput::make('amount_ves')
                                ->label('Monto (Bs)')
                                ->required()
                                ->numeric()
                                ->prefix('Bs')
                                ->default(0.00)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, $set) {
                                    $rate = \App\Models\ExchangeRate::latest('created_at')->value('rate') ?? 1;
                                    if ($rate > 0) {
                                        $set('amount_usd', round((float) $state / $rate, 2));
                                    }
                                }),
                        ]),
                        
                        FileUpload::make('receipt_path')
                            ->label('Comprobante (Factura o Recibo)')
                            ->directory('expenses-receipts')
                            ->image()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
