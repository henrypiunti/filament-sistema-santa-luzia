<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('flags low stock only when a minimum is configured and reached', function (int $stock, int $minimumStock, bool $isLowStock) {
    $product = Product::factory()->create(['stock' => $stock, 'minimum_stock' => $minimumStock]);

    expect($product->isLowStock())->toBe($isLowStock)
        ->and(Product::query()->lowStock()->whereKey($product)->exists())->toBe($isLowStock);
})->with([
    'below the minimum' => [5, 10, true],
    'exactly at the minimum' => [10, 10, true],
    'above the minimum' => [11, 10, false],
    'without a minimum and out of stock' => [0, 0, false],
]);

it('lists expired products and those expiring within the window', function (?int $expiresInDays, bool $isListed) {
    $this->travelTo(today()->setTime(10, 0));

    $product = Product::factory()->create([
        'expires_at' => $expiresInDays === null ? null : today()->addDays($expiresInDays),
    ]);

    expect(Product::query()->expiringWithin(30)->whereKey($product)->exists())->toBe($isListed);
})->with([
    'already expired' => [-1, true],
    'expiring today' => [0, true],
    'on the last day of the window' => [30, true],
    'one day after the window' => [31, false],
    'without an expiration date' => [null, false],
]);
