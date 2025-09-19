<?php

use App\Models\User;
use Livewire\Volt\Volt as LivewireVolt;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    // Test client user login (default role)
    $user = User::factory()->create(['role' => 'client']);

    $response = LivewireVolt::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('admin users are redirected to admin dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = LivewireVolt::test('auth.login')
        ->set('email', $admin->email)
        ->set('password', 'password')
        ->call('login');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('staff users are redirected to staff dashboard', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $response = LivewireVolt::test('auth.login')
        ->set('email', $staff->email)
        ->set('password', 'password')
        ->call('login');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('staff.dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('driver users are redirected to driver dashboard', function () {
    $driver = User::factory()->create(['role' => 'driver']);

    $response = LivewireVolt::test('auth.login')
        ->set('email', $driver->email)
        ->set('password', 'password')
        ->call('login');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('driver.dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = LivewireVolt::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'wrong-password')
        ->call('login');

    $response->assertHasErrors('email');

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('home'));

    $this->assertGuest();
});
