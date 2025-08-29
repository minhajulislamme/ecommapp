<?php

test('example unit test', function () {
    expect(true)->toBeTrue();
});

test('basic math operations', function () {
    expect(1 + 1)->toBe(2);
    expect(2 * 3)->toBe(6);
});

test('string operations', function () {
    expect('Laravel')->toBeString();
    expect(strlen('test'))->toBe(4);
});
