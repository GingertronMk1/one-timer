<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('user passwords are hashed and excluded from serialized data', function () {
    $user = new User(['name' => 'Jane', 'email' => 'jane@example.com', 'password' => 'password']);

    expect(Hash::check('password', $user->password))->toBeTrue();
    expect($user->toArray())->not->toHaveKeys(['password', 'remember_token']);
});
