<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Order;
use App\Models\Product;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class SalesOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|array|null $columns = ['md' => 2, 'xl' => 4];

    protected function getStats(): array
    {
        $currentMonthOrders = Order::query()->where('created_at', '>=', now()->startOfMonth());
        $previousMonthRevenue = (float) Order::query()
            ->whereBetween('created_at', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])
            ->sum('total');

        $revenue = (float) (clone $currentMonthOrders)->sum('total');
        $ordersCount = (clone $currentMonthOrders)->count();
        $averageTicket = $ordersCount > 0 ? $revenue / $ordersCount : 0;
        $lowStockCount = Product::query()->lowStock()->count();

        return [
            Stat::make('Vendas no mês', Number::currency($revenue, 'BRL', 'pt_BR'))
                ->description(self::describeVariation($revenue, $previousMonthRevenue))
                ->descriptionIcon($revenue >= $previousMonthRevenue ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown)
                ->color($revenue >= $previousMonthRevenue ? 'success' : 'danger')
                ->chart(Order::dailyRevenue(7)->values()->all()),
            Stat::make('Pedidos no mês', $ordersCount)
                ->description('Desde '.now()->startOfMonth()->format('d/m'))
                ->descriptionIcon(Heroicon::ShoppingCart)
                ->color('info'),
            Stat::make('Ticket médio', Number::currency($averageTicket, 'BRL', 'pt_BR'))
                ->description('Valor médio por pedido')
                ->descriptionIcon(Heroicon::Banknotes)
                ->color('primary'),
            Stat::make('Estoque baixo', $lowStockCount)
                ->description($lowStockCount > 0 ? 'Produtos precisando de reposição' : 'Estoque em dia')
                ->descriptionIcon($lowStockCount > 0 ? Heroicon::ExclamationTriangle : Heroicon::CheckCircle)
                ->color($lowStockCount > 0 ? 'danger' : 'success')
                ->url(ProductResource::getUrl('index', ['filters' => ['low_stock' => ['isActive' => true]]])),
        ];
    }

    private static function describeVariation(float $current, float $previous): string
    {
        if ($previous <= 0) {
            return 'Sem vendas no mês anterior';
        }

        $variation = (($current - $previous) / $previous) * 100;

        return sprintf('%s%s%% em relação ao mês anterior', $variation >= 0 ? '+' : '', number_format($variation, 1, ',', '.'));
    }
}
