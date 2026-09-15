<?php

use App\Models\Role;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('operational users reach their own dashboard', function (string $role, string $route) {
    $user = User::factory()->create(['role_id' => Role::firstOrCreate(['name' => $role])->id, 'is_active' => true]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route($route));
    $this->get(route($route))->assertOk();
})->with([
    ['Owner', 'owner.dashboard'],
    ['Cashier', 'cashier.dashboard'],
]);

test('an unassigned account cannot access an operational dashboard', function () {
    $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertForbidden();
});
