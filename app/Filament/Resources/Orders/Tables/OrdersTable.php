<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Pedido')
                    ->prefix('#')
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->formatStateUsing(fn (Order $record): string => "{$record->customer->name} {$record->customer->last_name}")
                    ->searchable(['name', 'last_name'])
                    ->sortable(),
                TextColumn::make('order_products_count')
                    ->label('Itens')
                    ->counts('orderProducts')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
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
