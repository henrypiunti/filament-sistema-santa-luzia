<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ExpiringProducts extends TableWidget
{
    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Vencimentos próximos')
            ->description('Vencidos ou vencendo em até '.Product::EXPIRATION_WARNING_DAYS.' dias')
            ->query(fn (): Builder => Product::query()->expiringWithin(Product::EXPIRATION_WARNING_DAYS)->orderBy('expires_at'))
            ->defaultPaginationPageOption(5)
            ->paginationPageOptions([5])
            ->columns([
                TextColumn::make('name')
                    ->label('Produto')
                    ->weight('bold'),
                TextColumn::make('stock')
                    ->label('Estoque')
                    ->numeric(),
                TextColumn::make('expires_at')
                    ->label('Validade')
                    ->badge()
                    ->formatStateUsing(fn (Product $record): string => $record->expires_at->isPast()
                        ? 'Vencido em '.$record->expires_at->format('d/m')
                        : $record->expires_at->format('d/m/Y'))
                    ->color(fn (Product $record): string => $record->expires_at->isPast() ? 'danger' : 'warning'),
            ])
            ->recordUrl(fn (Product $record): string => ProductResource::getUrl('edit', ['record' => $record]))
            ->emptyStateHeading('Nenhum produto perto do vencimento')
            ->emptyStateIcon(Heroicon::OutlinedCheckCircle);
    }
}
