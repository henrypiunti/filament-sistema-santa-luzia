<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Descrição')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('price')
                    ->label('Preço')
                    ->numeric()
                    ->required()
                    ->prefix('R$')
                    ->minValue(0),

                TextInput::make('stock')
                    ->label('Estoque atual')
                    ->integer()
                    ->required()
                    ->default(0)
                    ->minValue(0),

                TextInput::make('minimum_stock')
                    ->label('Estoque mínimo')
                    ->helperText('Um alerta é enviado quando o estoque atual chegar a este valor. Deixe 0 para não monitorar.')
                    ->integer()
                    ->required()
                    ->default(0)
                    ->minValue(0),

                DatePicker::make('expires_at')
                    ->label('Data de validade'),

                Select::make('categories')
                    ->label('Categorias')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
            ]);
    }
}
