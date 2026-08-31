<?php

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('renders customer pages', function () {
    Livewire::test(ListCustomers::class)->assertSuccessful();
    Livewire::test(CreateCustomer::class)->assertSuccessful();
    Livewire::test(EditCustomer::class, [
        'record' => Customer::factory()->create([
            'name' => 'Ana',
            'last_name' => 'Silva',
            'email' => 'ana@example.com',
            'cell_phone' => '11999999999',
            'zip_code' => '12345678',
            'street' => 'Rua A',
            'number' => '10',
            'neighborhood' => 'Centro',
            'city' => 'SP',
            'state' => 'SP',
        ])->getKey(),
    ])->assertSuccessful();
});

it('renders category pages', function () {
    Livewire::test(ListCategories::class)->assertSuccessful();
    Livewire::test(CreateCategory::class)->assertSuccessful();
    Livewire::test(EditCategory::class, [
        'record' => Category::factory()->create()->getKey(),
    ])->assertSuccessful();
});

it('renders product pages', function () {
    Livewire::test(ListProducts::class)->assertSuccessful();
    Livewire::test(CreateProduct::class)->assertSuccessful();
    Livewire::test(EditProduct::class, [
        'record' => Product::factory()->create()->getKey(),
    ])->assertSuccessful();
});

it('renders order pages and creates an order with products', function () {
    $customer = Customer::factory()->create([
        'name' => 'Ana',
        'last_name' => 'Silva',
        'email' => 'ana2@example.com',
        'cell_phone' => '11999999999',
        'zip_code' => '12345678',
        'street' => 'Rua A',
        'number' => '10',
        'neighborhood' => 'Centro',
        'city' => 'SP',
        'state' => 'SP',
    ]);
    $product = Product::factory()->create(['price' => 25.5]);

    Livewire::test(ListOrders::class)->assertSuccessful();

    Livewire::test(CreateOrder::class)
        ->fillForm([
            'customer_id' => $customer->id,
            'orderProducts' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'price' => 25.5,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $order = Order::first();
    expect($order)->not->toBeNull();
    expect((float) $order->total)->toBe(76.5);
    expect($order->orderProducts()->count())->toBe(1);

    Livewire::test(EditOrder::class, [
        'record' => $order->getKey(),
    ])->assertSuccessful();
});
