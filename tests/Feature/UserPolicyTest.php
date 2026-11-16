<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('only lets super-admins manage users', function (?string $roleName, bool $isAllowed) {
    $user = User::factory()->create();

    if ($roleName !== null) {
        $user->roles()->attach(Role::query()->where('name', $roleName)->value('id'));
    }

    $otherUser = User::factory()->create();

    expect($user->isSuperAdmin())->toBe($isAllowed)
        ->and($user->can('viewAny', User::class))->toBe($isAllowed)
        ->and($user->can('create', User::class))->toBe($isAllowed);

    foreach (['view', 'update', 'delete', 'restore', 'forceDelete'] as $ability) {
        expect($user->can($ability, $otherUser))->toBe($isAllowed, "Ability [{$ability}] mismatch.");
    }
})->with([
    'super-admin' => ['super-admin', true],
    'admin' => ['admin', false],
    'user' => ['user', false],
    'without roles' => [null, false],
]);
