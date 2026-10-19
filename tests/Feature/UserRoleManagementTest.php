<?php

use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('denies access to the users screen for an admin who is not a super-admin', function () {
    $adminRole = Role::where('name', 'admin')->first();

    $admin = User::factory()->create();
    $admin->roles()->attach($adminRole->id);
    $this->actingAs($admin);

    expect(UserResource::canViewAny())->toBeFalse();

    Livewire::test(ListUsers::class)->assertForbidden();
});

it('allows access to the users screen for a super-admin', function () {
    $superAdminRole = Role::where('name', 'super-admin')->first();

    $superAdmin = User::factory()->create();
    $superAdmin->roles()->attach($superAdminRole->id);
    $this->actingAs($superAdmin);

    expect(UserResource::canViewAny())->toBeTrue();

    Livewire::test(ListUsers::class)->assertSuccessful();
});

it('prevents a super-admin from removing their own super-admin role', function () {
    $superAdminRole = Role::where('name', 'super-admin')->first();
    $adminRole = Role::where('name', 'admin')->first();

    $user = User::factory()->create();
    $user->roles()->attach([$superAdminRole->id, $adminRole->id]);

    $this->actingAs($user);

    Livewire::test(EditUser::class, ['record' => $user->getKey()])
        ->fillForm([
            'roles' => [$adminRole->id],
        ])
        ->call('save')
        ->assertNotified('Você não pode remover sua própria role de super-admin.');

    expect($user->fresh()->roles()->pluck('name')->all())->toContain('super-admin');
});

it('allows a super-admin to change roles of another user', function () {
    $superAdminRole = Role::where('name', 'super-admin')->first();
    $userRole = Role::where('name', 'user')->first();
    $adminRole = Role::where('name', 'admin')->first();

    $actingUser = User::factory()->create();
    $actingUser->roles()->attach($superAdminRole->id);
    $this->actingAs($actingUser);

    $otherUser = User::factory()->create();
    $otherUser->roles()->attach($userRole->id);

    Livewire::test(EditUser::class, ['record' => $otherUser->getKey()])
        ->fillForm([
            'roles' => [$adminRole->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($otherUser->fresh()->roles()->pluck('name')->all())->toBe(['admin']);
});
