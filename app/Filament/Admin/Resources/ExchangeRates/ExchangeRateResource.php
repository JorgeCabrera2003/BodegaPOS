<?php

namespace App\Filament\Admin\Resources\ExchangeRates;

use App\Filament\Admin\Resources\ExchangeRates\Pages\CreateExchangeRate;
use App\Filament\Admin\Resources\ExchangeRates\Pages\EditExchangeRate;
use App\Filament\Admin\Resources\ExchangeRates\Pages\ListExchangeRates;
use App\Filament\Admin\Resources\ExchangeRates\Schemas\ExchangeRateForm;
use App\Filament\Admin\Resources\ExchangeRates\Tables\ExchangeRatesTable;
use App\Models\ExchangeRate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExchangeRateResource extends Resource
{
    protected static ?string $model = ExchangeRate::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Tasa BCV';
    protected static ?string $label = 'Tasa BCV';
    protected static ?string $pluralLabel = 'Tasas de Cambio';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Finanzas';
    }

    public static function form(Schema $schema): Schema
    {
        return ExchangeRateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExchangeRatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListExchangeRates::route('/'),
            'create' => CreateExchangeRate::route('/create'),
            'edit'   => EditExchangeRate::route('/{record}/edit'),
        ];
    }
}
