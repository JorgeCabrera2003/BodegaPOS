<?php

namespace App\Filament\Admin\Resources\Sales\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('cashier_id')
                    ->relationship('cashier', 'name')
                    ->required(),
                Select::make('exchange_rate_id')
                    ->relationship('exchangeRate', 'id')
                    ->required(),
                TextInput::make('customer_name'),
                TextInput::make('customer_phone')
                    ->tel(),
                TextInput::make('customer_id_number'),
                TextInput::make('subtotal_usd')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('igtf_usd')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_usd')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_ves')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('amount_paid_usd')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('change_usd')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'completed' => 'Completed',
            'refunded' => 'Refunded',
            'cancelled' => 'Cancelled',
        ])
                    ->default('pending')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
