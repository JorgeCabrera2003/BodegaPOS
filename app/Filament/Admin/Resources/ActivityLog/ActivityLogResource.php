<?php

namespace App\Filament\Admin\Resources\ActivityLog;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Bitácora';
    protected static ?string $label = 'Registro';
    protected static ?string $pluralLabel = 'Bitácora de Actividad';
    protected static ?int $navigationSort = 1;

    // Solo lectura — sin creación manual
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Sistema';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha / Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->timezone('America/Caracas'),

                Tables\Columns\TextColumn::make('log_name')
                    ->label('Módulo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sales'     => 'success',
                        'products'  => 'info',
                        'expenses'  => 'danger',
                        'payments'  => 'warning',
                        'exchange_rates' => 'primary',
                        'default'   => 'gray',
                        default     => 'primary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sales'     => '🧾 Ventas',
                        'products'  => '📦 Productos',
                        'expenses'  => '💸 Egresos',
                        'exchange_rates' => '💱 Tasas BCV',
                        'payments'  => '💳 Pagos',
                        'default'   => '⚙️ Sistema',
                        default     => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable()
                    ->wrap()
                    ->formatStateUsing(function (string $state): string {
                        // Traducir los verbos automáticos de Spatie ActivityLog
                        return str_replace(
                            ['created', 'updated', 'deleted', 'fue'], 
                            ['creado', 'actualizado', 'eliminado', 'ha sido'], 
                            $state
                        );
                    }),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Usuario')
                    ->searchable()
                    ->default('Sistema')
                    ->icon('heroicon-o-user'),

                Tables\Columns\TextColumn::make('event')
                    ->label('Evento')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'created' => '➕ Creado',
                        'updated' => '✏️ Modificado',
                        'deleted' => '🗑️ Eliminado',
                        default   => $state ?? 'N/A',
                    }),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Entidad')
                    ->formatStateUsing(fn (?string $state): string => match($state ? class_basename($state) : null) {
                        'Expense' => 'Egreso',
                        'Sale' => 'Venta',
                        'Product' => 'Producto',
                        'Payment' => 'Pago',
                        'ExchangeRate' => 'Tasa BCV',
                        'User' => 'Usuario',
                        'Role' => 'Rol',
                        null => 'N/A',
                        default => class_basename($state),
                    })
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->label('Módulo')
                    ->options([
                        'sales'    => '🧾 Ventas',
                        'products' => '📦 Productos',
                        'expenses' => '💸 Egresos',
                        'exchange_rates' => '💱 Tasas BCV',
                        'payments' => '💳 Pagos',
                        'default'  => '⚙️ Sistema',
                    ]),
                Tables\Filters\SelectFilter::make('event')
                    ->label('Evento')
                    ->options([
                        'created' => '➕ Creado',
                        'updated' => '✏️ Modificado',
                        'deleted' => '🗑️ Eliminado',
                    ]),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
