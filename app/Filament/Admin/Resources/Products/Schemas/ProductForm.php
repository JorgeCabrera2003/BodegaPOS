<?php

namespace App\Filament\Admin\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'xl' => 3])
            ->components([
                // Bloque Superior (Ocupa todo el ancho)
                Grid::make(1)->schema([
                    Section::make('Información Principal')->schema([
                        TextInput::make('name')
                            ->label('Nombre del Producto')
                            ->required()
                            ->maxLength(255),
                            
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])->columns(1),
                    
                    Section::make('Precios e Identificación')->schema([
                        TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(255),
                            
                        TextInput::make('barcode')
                            ->label('Código de Barras')
                            ->maxLength(255),
                            
                        TextInput::make('base_price_usd')
                            ->label('Precio Base (USD)')
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                            
                        TextInput::make('cost_price_usd')
                            ->label('Precio de Costo (USD)')
                            ->numeric()
                            ->prefix('$'),
                    ])->columns(2),
                ])->columnSpanFull(),
                
                // Fila Inferior (3 secciones, una al lado de la otra)
                Section::make('Multimedia')->schema([
                    FileUpload::make('image_path')
                        ->label('Imagen del Producto')
                        ->image()
                        ->imageEditor()
                        ->directory('products')
                        ->columnSpanFull(),
                ])
                ->columnSpan(['default' => 1, 'xl' => 1])
                ->extraAttributes(['class' => 'h-full']),
                
                Section::make('Clasificación')->schema([
                    Select::make('category_id')
                        ->label('Categoría')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload(),
                        
                    Select::make('supplier_id')
                        ->label('Proveedor')
                        ->relationship('supplier', 'name')
                        ->searchable()
                        ->preload(),
                ])
                ->columnSpan(['default' => 1, 'xl' => 1])
                ->extraAttributes(['class' => 'h-full']),
                
                Section::make('Inventario y Estado')->schema([
                    TextInput::make('stock_quantity')
                        ->label('Stock Actual')
                        ->required()
                        ->numeric()
                        ->default(0),
                        
                    Grid::make(2)->schema([
                        TextInput::make('min_stock_alert')
                            ->label('Stock Mínimo')
                            ->required()
                            ->numeric()
                            ->default(5),
                            
                        TextInput::make('max_stock')
                            ->label('Stock Máximo')
                            ->numeric(),
                    ]),
                    
                    Toggle::make('is_active')
                        ->label('Activo (Visible)')
                        ->default(true)
                        ->required(),
                        
                    Toggle::make('apply_igtf')
                        ->label('Aplica IGTF')
                        ->default(false)
                        ->required(),
                ])
                ->columnSpan(['default' => 1, 'xl' => 1])
                ->extraAttributes(['class' => 'h-full']),
            ]);
    }
}
