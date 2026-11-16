<?php

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

/**
 * @return array<string, string>
 */
function validCustomerData(): array
{
    return [
        'name' => 'Ana',
        'last_name' => 'Souza',
        'email' => 'ana.souza@example.com',
        'cell_phone' => '31988887777',
        'zip_code' => '33010000',
        'street' => 'Rua Direita',
        'number' => '120',
        'neighborhood' => 'Centro',
        'city' => 'Santa Luzia',
        'state' => 'MG',
    ];
}

it('creates a customer with valid data', function () {
    Livewire::test(CreateCustomer::class)
        ->fillForm(validCustomerData())
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Customer::query()->where('email', 'ana.souza@example.com')->first())
        ->city->toBe('Santa Luzia')
        ->isAnonymized()->toBeFalse();
});

it('rejects invalid customer data', function (array $overrides, string $field, string $rule) {
    Livewire::test(CreateCustomer::class)
        ->fillForm([...validCustomerData(), ...$overrides])
        ->call('create')
        ->assertHasFormErrors([$field => $rule]);

    expect(Customer::query()->count())->toBe(0);
})->with([
    'missing name' => [['name' => ''], 'name', 'required'],
    'missing phone' => [['cell_phone' => ''], 'cell_phone', 'required'],
    'invalid e-mail' => [['email' => 'not-an-email'], 'email', 'email'],
    'short zip code' => [['zip_code' => '3301000'], 'zip_code', 'size'],
    'long state' => [['state' => 'MGS'], 'state', 'size'],
]);

it('does not allow two customers with the same e-mail', function () {
    Customer::factory()->create(['email' => 'ana.souza@example.com']);

    Livewire::test(CreateCustomer::class)
        ->fillForm(validCustomerData())
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique']);
});

it('does not allow two categories with the same name', function () {
    Category::factory()->create(['name' => 'Genéricos']);

    Livewire::test(CreateCategory::class)
        ->fillForm(['name' => 'Genéricos'])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique']);

    Livewire::test(CreateCategory::class)
        ->fillForm(['name' => 'Vitaminas'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Category::query()->pluck('name')->all())->toEqualCanonicalizing(['Genéricos', 'Vitaminas']);
});

it('creates a product linked to its categories', function () {
    $categories = Category::factory()->count(2)->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Dipirona 500 mg',
            'price' => 6.49,
            'stock' => 100,
            'minimum_stock' => 20,
            'expires_at' => today()->addYear()->toDateString(),
            'categories' => $categories->modelKeys(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::query()->where('name', 'Dipirona 500 mg')->first();

    expect($product->stock)->toBe(100)
        ->and($product->categories->modelKeys())->toEqualCanonicalizing($categories->modelKeys());
});

it('rejects negative product values', function (string $field) {
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Dipirona 500 mg',
            'price' => 6.49,
            'stock' => 100,
            'minimum_stock' => 20,
            $field => -1,
        ])
        ->call('create')
        ->assertHasFormErrors([$field => 'min']);

    expect(Product::query()->count())->toBe(0);
})->with(['price', 'stock', 'minimum_stock']);

it('lets a super-admin create a user with a hashed password and roles', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->roles()->attach(Role::query()->where('name', 'super-admin')->value('id'));
    $this->actingAs($superAdmin);

    $adminRoleId = Role::query()->where('name', 'admin')->value('id');

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Maria Atendente',
            'email' => 'maria@example.com',
            'password' => 'senha-segura-123',
            'roles' => [$adminRoleId],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::query()->where('email', 'maria@example.com')->first();

    expect(Hash::check('senha-segura-123', $user->password))->toBeTrue()
        ->and($user->roles->pluck('name')->all())->toBe(['admin'])
        ->and($user->isSuperAdmin())->toBeFalse();
});

it('requires a password and at least one role when creating a user', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->roles()->attach(Role::query()->where('name', 'super-admin')->value('id'));
    $this->actingAs($superAdmin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Maria Atendente',
            'email' => 'maria@example.com',
            'password' => '',
            'roles' => [],
        ])
        ->call('create')
        ->assertHasFormErrors(['password' => 'required', 'roles' => 'required']);
});
