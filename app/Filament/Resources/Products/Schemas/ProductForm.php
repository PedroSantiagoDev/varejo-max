<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id')
                    ->label('ID')
                    ->required(),
                TextInput::make('name')
                    ->label('Name')
                    ->required(),
                TextInput::make('unit_price')
                    ->label('Unit Price')
                    ->numeric()
                    ->required(),
            ]);
    }
}
