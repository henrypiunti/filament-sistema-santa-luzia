<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('Estoque')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (Product $record): string => $record->isLowStock() ? 'danger' : 'success'),
                TextColumn::make('minimum_stock')
                    ->label('Estoque mínimo')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('expires_at')
                    ->label('Validade')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—')
                    ->color(fn (Product $record): ?string => match (true) {
                        $record->expires_at === null => null,
                        $record->expires_at->isPast() => 'danger',
                        $record->expires_at->lte(today()->addDays(Product::EXPIRATION_WARNING_DAYS)) => 'warning',
                        default => null,
                    }),
                TextColumn::make('categories.name')
                    ->label('Categorias')
                    ->badge()
                    ->separator(','),
                TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('low_stock')
                    ->label('Estoque baixo')
                    ->query(fn (Builder $query): Builder => $query->lowStock()),
                Filter::make('expiring')
                    ->label('Vencidos ou próximos do vencimento')
                    ->query(fn (Builder $query): Builder => $query->expiringWithin(Product::EXPIRATION_WARNING_DAYS)),
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
