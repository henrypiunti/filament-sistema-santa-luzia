<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Filament\Resources\Customers\Actions\AnonymizeCustomerAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label('Sobrenome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cell_phone')
                    ->label('Celular')
                    ->searchable(),
                TextColumn::make('city')
                    ->label('Cidade')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('state')
                    ->label('UF')
                    ->sortable(),
                IconColumn::make('anonymized_at')
                    ->label('Anonimizado')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('anonymized_at')
                    ->label('Anonimizados')
                    ->nullable()
                    ->placeholder('Todos')
                    ->trueLabel('Somente anonimizados')
                    ->falseLabel('Somente ativos'),
            ])
            ->recordActions([
                EditAction::make(),
                AnonymizeCustomerAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
