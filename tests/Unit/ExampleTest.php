<?php

use App\TimeUnit;

test('expiry units have their expected values', function () {
    expect(TimeUnit::from('hours'))->toBe(TimeUnit::HOURS);
});
