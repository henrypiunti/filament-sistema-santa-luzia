<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestOrders extends TableWidget
{
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Últimos pedidos')
            ->query(fn (): Builder => Order::query()->with('customer')->withCount('orderProducts')->latest()->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('id')
                    ->label('Pedido')
                    ->prefix('#')
                    ->weight('bold'),
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->formatStateUsing(fn (Order $record): string => "{$record->customer->name} {$record->customer->last_name}")
                    ->description(fn (Order $record): string => "{$record->order_products_count} item(ns)"),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('BRL')
                    ->weight('bold')
                    ->color('primary'),
                TextColumn::make('created_at')
                    ->label('Quando')
                    ->since()
                    ->dateTimeTooltip('d/m/Y H:i'),
            ])
            ->recordUrl(fn (Order $record): string => OrderResource::getUrl('edit', ['record' => $record]))
            ->emptyStateHeading('Nenhum pedido ainda')
            ->emptyStateIcon(Heroicon::OutlinedShoppingCart);
    }
}
