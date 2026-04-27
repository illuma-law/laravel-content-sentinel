<?php

declare(strict_types=1);

use IllumaLaw\ContentSentinel\DTOs\GateResult;

it('identifies a block severity', function () {
    $result = new GateResult(
        gate: 'test_gate',
        passed: false,
        severity: 'block',
        message: 'Blocked.',
    );

    expect($result->isBlock())->toBeTrue()
        ->and($result->isWarning())->toBeFalse();
});

it('identifies a warning severity', function () {
    $result = new GateResult(
        gate: 'test_gate',
        passed: false,
        severity: 'warning',
        message: 'Warning.',
    );

    expect($result->isWarning())->toBeTrue()
        ->and($result->isBlock())->toBeFalse();
});

it('serialises to array correctly', function () {
    $result = new GateResult(
        gate: 'test_gate',
        passed: true,
        severity: 'info',
        message: 'All good.',
        metadata: ['key' => 'value'],
    );

    expect($result->toArray())->toBe([
        'gate' => 'test_gate',
        'passed' => true,
        'severity' => 'info',
        'message' => 'All good.',
        'metadata' => ['key' => 'value'],
    ]);
});

it('defaults metadata to empty array', function () {
    $result = new GateResult(
        gate: 'test_gate',
        passed: true,
        severity: 'info',
        message: 'All good.',
    );

    expect($result->metadata)->toBe([]);
});
