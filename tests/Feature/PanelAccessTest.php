<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a registered user to access the panel in production', function () {
    config(['app.env' => 'production']);

    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertSuccessful();
});

it('redirects guests to the login page', function () {
    config(['app.env' => 'production']);

    $this->get('/admin')
        ->assertRedirect('/admin/login');
});
