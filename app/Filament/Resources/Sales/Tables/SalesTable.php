<?php

namespace App\Filament\Resources\Sales\Tables;

use App\Filament\Resources\Sales\Actions\ImportSalesAction;
use App\Models\Sale;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sale_date')
                    ->label('Data da Venda')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Quantidade')
                    ->sortable(),
                TextColumn::make('product.unit_price')
                    ->label('Valor Unitário')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('total_price')
                    ->label('Valor Total')
                    ->state(fn (Sale $record): float => $record->quantity * $record->product->unit_price)
                    ->money('BRL'),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ])
            ->headerActions([
                ImportSalesAction::make(),
            ]);
    }
}
