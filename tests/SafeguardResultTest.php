<?php

declare(strict_types=1);

use IllumaLaw\ContentSentinel\DTOs\GateResult;
use IllumaLaw\ContentSentinel\DTOs\SafeguardResult;

it('reports blocks correctly', function () {
    $gateResult = new GateResult('test', false, 'block', 'Blocked.');

    $result = new SafeguardResult(
        passed: false,
        warnings: [],
        blocks: ['Blocked.'],
        gateResults: ['test' => $gateResult],
    );

    expect($result->hasBlocks())->toBeTrue()
        ->and($result->hasWarnings())->toBeFalse()
        ->and($result->passed)->toBeFalse();
});

it('reports warnings correctly', function () {
    $gateResult = new GateResult('test', false, 'warning', 'Warning.');

    $result = new SafeguardResult(
        passed: true,
        warnings: ['Warning.'],
        blocks: [],
        gateResults: ['test' => $gateResult],
    );

    expect($result->hasWarnings())->toBeTrue()
        ->and($result->hasBlocks())->toBeFalse()
        ->and($result->passed)->toBeTrue();
});

it('serialises to array correctly', function () {
    $gateResult = new GateResult('test', true, 'info', 'OK.');

    $result = new SafeguardResult(
        passed: true,
        warnings: [],
        blocks: [],
        gateResults: ['test' => $gateResult],
    );

    $array = $result->toArray();

    expect($array['passed'])->toBeTrue()
        ->and($array['warnings'])->toBe([])
        ->and($array['blocks'])->toBe([]);

    $gateResults = $array['gate_results'];
    if (is_array($gateResults)) {
        expect($gateResults)->toHaveKey('test')
            ->and($gateResults['test'])->toBeArray();
    }
});
