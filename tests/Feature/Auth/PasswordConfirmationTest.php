<?php

use App\Models\User;

test('confirm password screen can be rendered', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('password.confirm'));

    $response->assertOk()->assertDontSee('Sign in with a passkey')
        ->assertSee('ct-auth-card')->assertSee('CashTrack Secure')
        ->assertSee('name="password"', false)
        ->assertSee('action="'.route('password.confirm.store').'"', false);
});

test('normal password confirmation still accepts the correct password', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->post(route('password.confirm.store'), ['password' => 'password']);

    $response->assertSessionHasNoErrors()->assertSessionHas('auth.password_confirmed_at');
});

test('normal password confirmation still rejects the wrong password', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->post(route('password.confirm.store'), ['password' => 'incorrect']);

    $response->assertSessionHasErrors('password')->assertSessionMissing('auth.password_confirmed_at');
});
