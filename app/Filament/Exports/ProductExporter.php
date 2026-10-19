<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('name')
                ->label('Nome'),
            ExportColumn::make('description')
                ->label('Descrição'),
            ExportColumn::make('price')
                ->label('Preço'),
            ExportColumn::make('stock')
                ->label('Estoque'),
            ExportColumn::make('minimum_stock')
                ->label('Estoque mínimo'),
            ExportColumn::make('expires_at')
                ->label('Validade'),
            ExportColumn::make('categories.name')
                ->label('Categorias'),
            ExportColumn::make('created_at')
                ->label('Cadastrado em'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = "Exportação de produtos concluída: {$export->successful_rows} registro(s) exportado(s).";

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= " {$failedRowsCount} registro(s) falharam.";
        }

        return $body;
    }
}
