<?php

namespace App\Filament\Exports;

use App\Models\Order;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class OrderExporter extends Exporter
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('Pedido'),
            ExportColumn::make('customer.name')
                ->label('Cliente'),
            ExportColumn::make('customer.last_name')
                ->label('Sobrenome do cliente'),
            ExportColumn::make('order_products_count')
                ->label('Itens')
                ->counts('orderProducts'),
            ExportColumn::make('total')
                ->label('Total'),
            ExportColumn::make('created_at')
                ->label('Data'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = "Exportação de pedidos concluída: {$export->successful_rows} registro(s) exportado(s).";

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= " {$failedRowsCount} registro(s) falharam.";
        }

        return $body;
    }
}
