<?php

namespace App\Filament\Admin\Resources\ExchangeRates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExchangeRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('currency_code')
                    ->required()
                    ->default('USD'),
                TextInput::make('rate')
                    ->required()
                    ->numeric(),
                Select::make('source')
                    ->options(['BCV' => 'B c v', 'manual' => 'Manual'])
                    ->default('BCV')
                    ->required(),
                TextInput::make('notes'),
            ]);
    }
}
