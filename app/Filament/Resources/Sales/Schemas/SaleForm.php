<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id')
                    ->label('ID')
                    ->required(),
                Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->required(),
                Select::make('client_id')
                    ->label('Client')
                    ->relationship('client', 'name')
                    ->required(),
                TextInput::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->required(),
                DateTimePicker::make('sale_date')
                    ->label('Sale Date')
                    ->required(),
            ]);
    }
}
