<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Vendas dos últimos 30 dias';

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $revenueByDay = Order::dailyRevenue(30);

        return [
            'datasets' => [
                [
                    'label' => 'Vendas (R$)',
                    'data' => $revenueByDay->values()->all(),
                    'fill' => 'start',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $revenueByDay->keys()->map(fn (string $date): string => Carbon::parse($date)->format('d/m'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ];
    }
}
