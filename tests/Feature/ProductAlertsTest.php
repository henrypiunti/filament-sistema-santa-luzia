<?php

use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function userWithRole(string $roleName): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::where('name', $roleName)->first()->id);

    return $user;
}

it('notifies admins about products with low stock', function () {
    $admin = userWithRole('admin');
    $superAdmin = userWithRole('super-admin');
    $regularUser = userWithRole('user');

    Product::factory()->lowStock()->create(['name' => 'Arroz']);
    Product::factory()->create(['name' => 'Feijão']);

    $this->artisan('products:check-alerts')->assertSuccessful();

    foreach ([$admin, $superAdmin] as $recipient) {
        expect($recipient->notifications)->toHaveCount(1)
            ->and($recipient->notifications->first()->data['title'])->toBe('1 produto(s) com estoque baixo')
            ->and($recipient->notifications->first()->data['body'])->toContain('Arroz')->not->toContain('Feijão');
    }

    expect($regularUser->notifications)->toHaveCount(0);
});

it('notifies admins about expired and expiring products', function () {
    $admin = userWithRole('admin');

    Product::factory()->expiringIn(-1)->create(['name' => 'Leite vencido']);
    Product::factory()->expiringIn(10)->create(['name' => 'Iogurte']);
    Product::factory()->expiringIn(90)->create(['name' => 'Enlatado']);

    $this->artisan('products:check-alerts')->assertSuccessful();

    $notification = $admin->notifications->sole();

    expect($notification->data['title'])->toBe('2 produto(s) vencidos ou vencendo em até 30 dias')
        ->and($notification->data['body'])->toContain('Leite vencido', 'Iogurte')->not->toContain('Enlatado');
});

it('respects the days option for the expiration window', function () {
    $admin = userWithRole('admin');

    Product::factory()->expiringIn(10)->create();

    $this->artisan('products:check-alerts', ['--days' => 5])->assertSuccessful();

    expect($admin->notifications)->toHaveCount(0);
});

it('ignores products without a minimum stock configured', function () {
    $admin = userWithRole('admin');

    Product::factory()->create(['stock' => 0, 'minimum_stock' => 0]);

    $this->artisan('products:check-alerts')->assertSuccessful();

    expect($admin->notifications)->toHaveCount(0);
});

it('filters the products table by low stock and expiration', function () {
    $this->actingAs(User::factory()->create());

    $lowStock = Product::factory()->lowStock()->create();
    $expiring = Product::factory()->expiringIn(5)->create();
    $regular = Product::factory()->create();

    Livewire::test(ListProducts::class)
        ->filterTable('low_stock')
        ->assertCanSeeTableRecords([$lowStock])
        ->assertCanNotSeeTableRecords([$expiring, $regular])
        ->resetTableFilters()
        ->filterTable('expiring')
        ->assertCanSeeTableRecords([$expiring])
        ->assertCanNotSeeTableRecords([$lowStock, $regular]);
});
