<?php

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\OrderSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('seeds a consistent demo store', function () {
    $this->seed(DatabaseSeeder::class);

    expect(User::query()->where('email', 'admin@santaluzia.test')->first()->isSuperAdmin())->toBeTrue()
        ->and(Category::query()->count())->toBe(count(CategorySeeder::NAMES))
        ->and(Product::query()->count())->toBe(count(ProductSeeder::PRODUCTS))
        ->and(Customer::query()->count())->toBe(CustomerSeeder::TOTAL)
        ->and(Order::query()->count())->toBeGreaterThan(50)
        ->and(Product::query()->where('stock', '<', 0)->exists())->toBeFalse()
        ->and(Product::query()->lowStock()->exists())->toBeTrue()
        ->and(Product::query()->expiringWithin(Product::EXPIRATION_WARNING_DAYS)->exists())->toBeTrue();

    Order::query()->with('orderProducts')->each(function (Order $order): void {
        $itemsTotal = $order->orderProducts->sum(fn ($item): float => $item->quantity * (float) $item->price);

        expect((float) $order->total)->toBe(round($itemsTotal, 2));
    });
});

it('lowers the stock of the products sold', function () {
    $this->seed(DatabaseSeeder::class);

    $product = Product::query()->whereHas('orders')->first();
    $initialStock = collect(ProductSeeder::PRODUCTS)->firstWhere('name', $product->name)['stock'];

    expect($product->stock)->toBe($initialStock - (int) $product->orders()->sum('quantity'));
});

it('can run more than once without duplicating records', function () {
    $this->seed(DatabaseSeeder::class);
    $orders = Order::query()->count();

    $this->seed(DatabaseSeeder::class);

    expect(Customer::query()->count())->toBe(CustomerSeeder::TOTAL)
        ->and(Product::query()->count())->toBe(count(ProductSeeder::PRODUCTS))
        ->and(Order::query()->count())->toBe($orders);
});

it('links every product to categories that exist in the catalog', function () {
    $this->seed(DatabaseSeeder::class);

    foreach (ProductSeeder::PRODUCTS as $data) {
        expect(array_diff($data['categories'], CategorySeeder::NAMES))->toBeEmpty("Unknown category in [{$data['name']}].");

        $product = Product::query()->where('name', $data['name'])->first();

        expect($product->categories->pluck('name')->all())->toEqualCanonicalizing($data['categories']);
    }
});

it('only leaves low on stock the products that start below the minimum', function () {
    $this->seed(DatabaseSeeder::class);

    $productsStartingLow = collect(ProductSeeder::PRODUCTS)
        ->filter(fn (array $data): bool => $data['minimum_stock'] > 0 && $data['stock'] <= $data['minimum_stock'])
        ->pluck('name');

    expect(Product::query()->lowStock()->pluck('name')->all())->toEqualCanonicalizing($productsStartingLow->all());
});

it('seeds customers with valid and unique contact data', function () {
    $this->seed(DatabaseSeeder::class);

    $customers = Customer::query()->get();

    expect($customers->pluck('email')->unique())->toHaveCount(CustomerSeeder::TOTAL);

    $customers->each(function (Customer $customer): void {
        expect($customer->email)->toMatch('/^[a-z0-9._]+@[a-z.]+$/')
            ->and($customer->cell_phone)->toMatch('/^\d{2}9\d{8}$/')
            ->and($customer->zip_code)->toMatch('/^\d{8}$/')
            ->and($customer->state)->toHaveLength(2)
            ->and($customer->isAnonymized())->toBeFalse()
            ->and($customer->created_at->isPast())->toBeTrue();
    });
});

it('spreads the orders over the history window using the product prices', function () {
    $this->seed(DatabaseSeeder::class);

    $oldestAllowed = today()->subDays(OrderSeeder::DAYS_OF_HISTORY - 1);

    Order::query()->with('orderProducts.product')->each(function (Order $order) use ($oldestAllowed): void {
        expect($order->created_at->greaterThanOrEqualTo($oldestAllowed))->toBeTrue()
            ->and($order->created_at->isFuture())->toBeFalse()
            ->and($order->orderProducts)->not->toBeEmpty();

        $order->orderProducts->each(function ($item): void {
            expect($item->price)->toBe($item->product->price)
                ->and($item->quantity)->toBeBetween(1, 3);
        });
    });

    expect(Order::query()->where('created_at', '>=', today()->subDays(6))->exists())->toBeTrue();
});

it('keeps the existing admin password when seeding again', function () {
    $this->seed(DatabaseSeeder::class);

    User::query()->where('email', 'admin@santaluzia.test')->first()->update(['password' => 'nova-senha']);

    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('email', 'admin@santaluzia.test')->first();

    expect(Hash::check('nova-senha', $admin->password))->toBeTrue()
        ->and($admin->roles()->where('name', 'super-admin')->count())->toBe(1);
});

it('does not create orders without active customers', function () {
    $this->seed([CategorySeeder::class, ProductSeeder::class]);
    Customer::factory()->anonymized()->create();

    $this->seed(OrderSeeder::class);

    expect(Order::query()->count())->toBe(0);
});
