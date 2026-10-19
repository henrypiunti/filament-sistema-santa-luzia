<?php

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Widgets\ExpiringProducts;
use App\Filament\Widgets\LatestOrders;
use App\Filament\Widgets\SalesChart;
use App\Filament\Widgets\SalesOverview;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Pages\Dashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('renders the dashboard with all widgets', function () {
    Livewire::test(Dashboard::class)
        ->assertSuccessful()
        ->assertSeeLivewire(SalesOverview::class)
        ->assertSeeLivewire(SalesChart::class)
        ->assertSeeLivewire(LatestOrders::class)
        ->assertSeeLivewire(ExpiringProducts::class);
});

it('shows the current month sales figures', function () {
    Order::factory()->create(['total' => 100]);
    Order::factory()->create(['total' => 50]);
    Order::factory()->create(['total' => 999, 'created_at' => now()->subMonthsNoOverflow(2)]);
    Product::factory()->lowStock()->create();

    Livewire::test(SalesOverview::class)
        ->assertSee("R$\u{00A0}150,00")
        ->assertSee("R$\u{00A0}75,00")
        ->assertSee('Produtos precisando de reposição');
});

it('computes the daily revenue including days without sales', function () {
    Order::factory()->create(['total' => 40, 'created_at' => today()->subDays(2)]);
    Order::factory()->create(['total' => 60, 'created_at' => today()->subDays(2)]);
    Order::factory()->create(['total' => 25]);

    expect(Order::dailyRevenue(3)->all())->toBe([
        today()->subDays(2)->toDateString() => 100.0,
        today()->subDay()->toDateString() => 0.0,
        today()->toDateString() => 25.0,
    ]);
});

it('lists the latest orders and expiring products', function () {
    $orders = Order::factory()->count(6)->sequence(fn ($sequence) => ['created_at' => now()->subMinutes($sequence->index)])->create();
    $expiring = Product::factory()->expiringIn(3)->create();
    $notExpiring = Product::factory()->expiringIn(90)->create();

    Livewire::test(LatestOrders::class)
        ->assertCanSeeTableRecords($orders->take(5))
        ->assertCanNotSeeTableRecords([$orders->last()]);

    Livewire::test(ExpiringProducts::class)
        ->assertCanSeeTableRecords([$expiring])
        ->assertCanNotSeeTableRecords([$notExpiring]);
});

it('shows the low stock badge in the navigation', function () {
    expect(ProductResource::getNavigationBadge())->toBeNull();

    Product::factory()->lowStock()->count(2)->create();

    expect(ProductResource::getNavigationBadge())->toBe('2');
});
