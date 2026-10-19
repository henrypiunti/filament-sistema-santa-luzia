<?php

namespace App\Filament\Exports;

use App\Models\Customer;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CustomerExporter extends Exporter
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('name')
                ->label('Nome'),
            ExportColumn::make('last_name')
                ->label('Sobrenome'),
            ExportColumn::make('email')
                ->label('E-mail'),
            ExportColumn::make('cell_phone')
                ->label('Celular'),
            ExportColumn::make('zip_code')
                ->label('CEP'),
            ExportColumn::make('street')
                ->label('Rua'),
            ExportColumn::make('number')
                ->label('Número'),
            ExportColumn::make('complement')
                ->label('Complemento'),
            ExportColumn::make('neighborhood')
                ->label('Bairro'),
            ExportColumn::make('city')
                ->label('Cidade'),
            ExportColumn::make('state')
                ->label('UF'),
            ExportColumn::make('created_at')
                ->label('Cadastrado em'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = "Exportação de clientes concluída: {$export->successful_rows} registro(s) exportado(s).";

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= " {$failedRowsCount} registro(s) falharam.";
        }

        return $body;
    }
}
