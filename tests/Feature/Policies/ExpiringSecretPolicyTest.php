<?php

use App\Models\ExpiringSecret;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('the secret policy denies model abilities for users', function (string $ability) {
    $user = User::factory()->make();
    $secret = new ExpiringSecret;

    expect(Gate::forUser($user)->allows($ability, $secret))->toBeFalse();
})->with(['view', 'update', 'delete', 'restore', 'forceDelete']);

test('the secret policy denies listing and creating secrets for users', function (string $ability) {
    $user = User::factory()->make();

    expect(Gate::forUser($user)->allows($ability, ExpiringSecret::class))->toBeFalse();
})->with(['viewAny', 'create']);
