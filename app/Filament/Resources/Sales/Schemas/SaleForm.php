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
                Select::make('product_id')
                    ->label('Produto')
                    ->relationship('product', 'name')
                    ->required(),
                Select::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'name')
                    ->required(),
                TextInput::make('quantity')
                    ->label('Quantidade')
                    ->numeric()
                    ->required(),
                DateTimePicker::make('sale_date')
                    ->label('Data da Venda')
                    ->required(),
            ]);
    }
}
