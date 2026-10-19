<?php

use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Testing\TestAction;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('anonymizes the personal data and keeps the orders', function () {
    $customer = Customer::factory()->create(['city' => 'Santa Luzia', 'state' => 'MG', 'complement' => 'Apto 12']);
    $order = Order::factory()->for($customer)->create(['total' => 150]);

    Livewire::test(EditCustomer::class, ['record' => $customer->getKey()])
        ->callAction('anonymize')
        ->assertNotified('Dados do cliente anonimizados');

    $customer->refresh();

    expect($customer->isAnonymized())->toBeTrue()
        ->and($customer->only(['name', 'last_name', 'email', 'cell_phone', 'zip_code', 'street', 'number', 'complement', 'neighborhood']))
        ->toBe([
            'name' => 'Cliente',
            'last_name' => 'Anonimizado',
            'email' => "anonimizado-{$customer->id}@anonimizado.invalid",
            'cell_phone' => '00000000000',
            'zip_code' => '00000000',
            'street' => 'Anonimizado',
            'number' => 'Anonimizado',
            'complement' => null,
            'neighborhood' => 'Anonimizado',
        ])
        ->and($customer->only(['city', 'state']))->toBe(['city' => 'Santa Luzia', 'state' => 'MG'])
        ->and($order->fresh())->not->toBeNull()
        ->and((float) $order->fresh()->total)->toBe(150.0);
});

it('anonymizes from the customers table', function () {
    $customer = Customer::factory()->create();

    Livewire::test(ListCustomers::class)
        ->callAction(TestAction::make('anonymize')->table($customer));

    expect($customer->fresh()->isAnonymized())->toBeTrue();
});

it('makes anonymized customers read-only', function () {
    $customer = Customer::factory()->anonymized()->create();

    Livewire::test(EditCustomer::class, ['record' => $customer->getKey()])
        ->assertActionHidden('anonymize')
        ->assertFormFieldIsDisabled('name')
        ->assertFormFieldIsDisabled('email');

    Livewire::test(ListCustomers::class)
        ->assertActionHidden(TestAction::make('anonymize')->table($customer));
});

it('hides deletion for customers with orders', function () {
    $customer = Customer::factory()->create();
    Order::factory()->for($customer)->create();

    Livewire::test(EditCustomer::class, ['record' => $customer->getKey()])
        ->assertActionHidden(DeleteAction::class);
});

it('still deletes customers without orders', function () {
    $customer = Customer::factory()->create();

    Livewire::test(EditCustomer::class, ['record' => $customer->getKey()])
        ->callAction(DeleteAction::class);

    expect(Customer::count())->toBe(0);
});

it('prevents deleting a customer with orders at the database level', function () {
    $customer = Customer::factory()->create();
    Order::factory()->for($customer)->create();

    expect(fn () => $customer->delete())->toThrow(QueryException::class);
    expect(Order::count())->toBe(1);
});

it('prunes old exports and their files', function () {
    Storage::fake('local');

    $makeExport = fn () => Export::forceCreate([
        'user_id' => auth()->id(),
        'exporter' => 'App\Filament\Exports\CustomerExporter',
        'file_disk' => 'local',
        'file_name' => 'clientes',
        'total_rows' => 1,
    ]);

    $oldExport = $makeExport();
    $oldExport->forceFill(['created_at' => now()->subDays(8)])->save();
    $recentExport = $makeExport();

    Storage::disk('local')->put("filament_exports/{$oldExport->id}/clientes.xlsx", 'dados');
    Storage::disk('local')->put("filament_exports/{$recentExport->id}/clientes.xlsx", 'dados');

    $this->artisan('exports:prune')->assertSuccessful();

    expect(Export::pluck('id')->all())->toBe([$recentExport->id]);
    Storage::disk('local')->assertMissing("filament_exports/{$oldExport->id}/clientes.xlsx");
    Storage::disk('local')->assertExists("filament_exports/{$recentExport->id}/clientes.xlsx");
});
