<?php

namespace App\Filament\Admin\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Imagen')
                    ->square(),
                    
                TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('base_price_usd')
                    ->label('Precio Base')
                    ->money('USD')
                    ->sortable(),
                    
                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state, $record) => $state <= $record->min_stock_alert ? 'danger' : 'success'),
                    
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                    
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('barcode')
                    ->label('Código de Barras')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('cost_price_usd')
                    ->label('Costo')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('min_stock_alert')
                    ->label('Stock Mínimo')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('max_stock')
                    ->label('Stock Máximo')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                IconColumn::make('apply_igtf')
                    ->label('Aplica IGTF')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('deleted_at')
                    ->label('Eliminado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('restock')
                    ->label('Surtir')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('quantity_to_add')
                            ->label('Cantidad a ingresar')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                    ])
                    ->action(function ($record, array $data): void {
                        $record->stock_quantity += $data['quantity_to_add'];
                        $record->save();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Inventario actualizado')
                            ->body("Se han sumado {$data['quantity_to_add']} unidades al producto {$record->name}.")
                            ->success()
                            ->send();
                    })
                    ->modalHeading(fn ($record) => 'Surtir: ' . $record->name)
                    ->modalDescription(fn ($record) => "Stock actual: {$record->stock_quantity}. Ingresa la cantidad de unidades nuevas que llegaron.")
                    ->modalSubmitActionLabel('Sumar al inventario')
                    ->modalWidth('sm'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
