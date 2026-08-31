<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados pessoais')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label('Sobrenome')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('cell_phone')
                            ->label('Celular')
                            ->tel()
                            ->required()
                            ->maxLength(20),
                    ]),

                Section::make('Endereço')
                    ->columns(3)
                    ->components([
                        TextInput::make('zip_code')
                            ->label('CEP')
                            ->required()
                            ->length(8),
                        TextInput::make('street')
                            ->label('Rua')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        TextInput::make('number')
                            ->label('Número')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('complement')
                            ->label('Complemento')
                            ->maxLength(255)
                            ->columnSpan(2),
                        TextInput::make('neighborhood')
                            ->label('Bairro')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('city')
                            ->label('Cidade')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('state')
                            ->label('UF')
                            ->required()
                            ->length(2),
                    ]),
            ]);
    }
}
