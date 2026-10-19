<?php

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('decreases product stock when an order is created', function () {
    $product = Product::factory()->create(['stock' => 10]);

    Livewire::test(CreateOrder::class)
        ->fillForm([
            'customer_id' => Customer::factory()->create()->id,
            'orderProducts' => [
                ['product_id' => $product->id, 'quantity' => 3, 'price' => 10],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect($product->fresh()->stock)->toBe(7);
});

it('blocks orders with quantity above the available stock', function () {
    $product = Product::factory()->create(['stock' => 2]);

    Livewire::test(CreateOrder::class)
        ->fillForm([
            'customer_id' => Customer::factory()->create()->id,
            'orderProducts' => [
                ['product_id' => $product->id, 'quantity' => 5, 'price' => 10],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors();

    expect(Order::count())->toBe(0)
        ->and($product->fresh()->stock)->toBe(2);
});

it('adjusts stock when an item quantity changes', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $item = Order::factory()->create()->orderProducts()->create(['product_id' => $product->id, 'quantity' => 4, 'price' => 10]);

    expect($product->fresh()->stock)->toBe(6);

    $item->update(['quantity' => 1]);
    expect($product->fresh()->stock)->toBe(9);

    $item->update(['quantity' => 9]);
    expect($product->fresh()->stock)->toBe(1);
});

it('moves stock between products when an item product changes', function () {
    $oldProduct = Product::factory()->create(['stock' => 10]);
    $newProduct = Product::factory()->create(['stock' => 10]);
    $item = Order::factory()->create()->orderProducts()->create(['product_id' => $oldProduct->id, 'quantity' => 3, 'price' => 10]);

    $item->update(['product_id' => $newProduct->id, 'quantity' => 2]);

    expect($oldProduct->fresh()->stock)->toBe(10)
        ->and($newProduct->fresh()->stock)->toBe(8);
});

it('restores stock when an item is removed from the order', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $item = Order::factory()->create()->orderProducts()->create(['product_id' => $product->id, 'quantity' => 4, 'price' => 10]);

    $item->delete();

    expect($product->fresh()->stock)->toBe(10);
});

it('allows keeping the same quantity when editing an order that used all the stock', function () {
    $product = Product::factory()->create(['stock' => 5]);
    $order = Order::factory()->create();
    $order->orderProducts()->create(['product_id' => $product->id, 'quantity' => 5, 'price' => 10]);

    Livewire::test(EditOrder::class, ['record' => $order->getKey()])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->fresh()->stock)->toBe(0);
});

it('restores stock when an order is deleted from the edit page', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $order = Order::factory()->create();
    $order->orderProducts()->create(['product_id' => $product->id, 'quantity' => 4, 'price' => 10]);

    Livewire::test(EditOrder::class, ['record' => $order->getKey()])
        ->callAction(DeleteAction::class);

    expect(Order::count())->toBe(0)
        ->and($product->fresh()->stock)->toBe(10);
});

it('restores stock when orders are bulk deleted', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $orders = Order::factory()->count(2)->create();
    $orders->each(fn (Order $order) => $order->orderProducts()->create(['product_id' => $product->id, 'quantity' => 2, 'price' => 10]));

    expect($product->fresh()->stock)->toBe(6);

    Livewire::test(ListOrders::class)
        ->selectTableRecords($orders)
        ->callAction(TestAction::make('delete')->table()->bulk());

    expect(Order::count())->toBe(0)
        ->and($product->fresh()->stock)->toBe(10);
});
