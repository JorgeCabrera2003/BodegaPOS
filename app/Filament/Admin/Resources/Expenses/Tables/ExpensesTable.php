<?php

namespace App\Filament\Admin\Resources\Expenses\Tables;

use App\Models\Expense;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;

class ExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expense_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                TextColumn::make('category')
                    ->label('Categoría')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Expense::$categoryLabels[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'supplier' => 'primary',
                        'payroll' => 'warning',
                        'utilities' => 'info',
                        'rent' => 'danger',
                        default => 'gray',
                    }),
                    
                TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(30),
                    
                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->searchable()
                    ->toggleable(),
                    
                TextColumn::make('amount_usd')
                    ->label('Monto ($)')
                    ->money('usd')
                    ->sortable()
                    ->color('danger')
                    ->weight('bold'),
                    
                TextColumn::make('amount_ves')
                    ->label('Monto (Bs)')
                    ->numeric(2)
                    ->prefix('Bs ')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('registeredBy.name')
                    ->label('Registrado por')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                IconColumn::make('is_recurring')
                    ->label('Recurrente')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('expense_date', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->label('Categoría')
                    ->options(Expense::$categoryLabels),
                    
                Filter::make('expense_date')
                    ->form([
                        DatePicker::make('created_from')->label('Desde'),
                        DatePicker::make('created_until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expense_date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expense_date', '<=', $date),
                            );
                    })
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
