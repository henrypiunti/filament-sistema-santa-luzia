<?php

use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('exports customers to xlsx', function () {
    Customer::factory()->count(2)->sequence(
        [
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
        ],
        [
            'name' => 'Bruno',
            'last_name' => 'Souza',
            'email' => 'bruno@example.com',
            'cell_phone' => '11988888888',
            'zip_code' => '87654321',
            'street' => 'Rua B',
            'number' => '20',
            'neighborhood' => 'Centro',
            'city' => 'RJ',
            'state' => 'RJ',
        ],
    )->create();

    Livewire::test(ListCustomers::class)
        ->callAction('export')
        ->assertHasNoActionErrors();

    $export = Export::latest()->first();

    expect($export)->not->toBeNull()
        ->and($export->successful_rows)->toBe(2)
        ->and($export->getFailedRowsCount())->toBe(0);
});

it('exports products to xlsx', function () {
    Product::factory()->count(2)->create();

    Livewire::test(ListProducts::class)
        ->callAction('export')
        ->assertHasNoActionErrors();

    $export = Export::latest()->first();

    expect($export)->not->toBeNull()
        ->and($export->successful_rows)->toBe(2)
        ->and($export->getFailedRowsCount())->toBe(0);
});

it('exports orders to xlsx', function () {
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

    Order::factory()->create([
        'customer_id' => $customer->id,
        'total' => 100,
    ]);

    Livewire::test(ListOrders::class)
        ->callAction('export')
        ->assertHasNoActionErrors();

    $export = Export::latest()->first();

    expect($export)->not->toBeNull()
        ->and($export->successful_rows)->toBe(1)
        ->and($export->getFailedRowsCount())->toBe(0);
});
