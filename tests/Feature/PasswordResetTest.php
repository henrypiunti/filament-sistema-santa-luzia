<?php

use App\Models\User;
use Filament\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Filament\Auth\Pages\PasswordReset\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows the forgot password link on the login page', function () {
    $this->get(route('filament.admin.auth.login'))
        ->assertOk()
        ->assertSee(route('filament.admin.auth.password-reset.request'), escape: false);
});

it('sends a password reset notification to an existing user', function () {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test(RequestPasswordReset::class)
        ->fillForm(['email' => $user->email])
        ->call('request')
        ->assertHasNoFormErrors();

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

it('resets the password with a valid token', function () {
    $user = User::factory()->create();
    $token = Password::broker()->createToken($user);

    Livewire::test(ResetPassword::class, ['email' => $user->email, 'token' => $token])
        ->fillForm([
            'password' => 'nova-senha-segura',
            'passwordConfirmation' => 'nova-senha-segura',
        ])
        ->call('resetPassword')
        ->assertHasNoFormErrors();

    expect(Hash::check('nova-senha-segura', $user->fresh()->password))->toBeTrue();
});
