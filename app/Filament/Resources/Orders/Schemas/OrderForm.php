<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Customer;
use App\Models\Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Customer $record): string => "{$record->name} {$record->last_name}")
                    ->searchable(['name', 'last_name', 'email'])
                    ->preload()
                    ->required(),

                Repeater::make('orderProducts')
                    ->label('Produtos')
                    ->relationship()
                    ->live()
                    ->schema([
                        Select::make('product_id')
                            ->label('Produto')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                $set('price', Product::find($state)?->price);
                            })
                            ->columnSpan(2),
                        TextInput::make('quantity')
                            ->label('Quantidade')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->default(1)
                            ->required()
                            ->live(onBlur: true),
                        TextInput::make('price')
                            ->label('Preço unitário')
                            ->numeric()
                            ->prefix('R$')
                            ->minValue(0)
                            ->required()
                            ->live(onBlur: true),
                    ])
                    ->columns(4)
                    ->addActionLabel('Adicionar produto')
                    ->defaultItems(1)
                    ->required()
                    ->minItems(1)
                    ->columnSpanFull(),

                TextEntry::make('total_preview')
                    ->label('Total do pedido')
                    ->state(function (Get $get): string {
                        $total = collect($get('orderProducts') ?? [])
                            ->sum(fn (array $item): float => (float) ($item['quantity'] ?? 0) * (float) ($item['price'] ?? 0));

                        return 'R$ '.number_format($total, 2, ',', '.');
                    })
                    ->columnSpanFull(),
            ]);
    }
}
