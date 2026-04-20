<?php

declare(strict_types=1);

use IllumaLaw\ContentSentinel\DTOs\GateResult;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;

it('builds full content from content only', function () {
    $payload = new SentinelPayload(content: 'Hello world');

    expect($payload->getFullContent())->toBe('Hello world');
});

it('builds full content including title and caption', function () {
    $payload = new SentinelPayload(
        content: 'Body text',
        title: 'My Title',
        caption: 'My Caption',
    );

    expect($payload->getFullContent())->toBe('Body text My Title My Caption');
});

it('trims whitespace when title and caption are null', function () {
    $payload = new SentinelPayload(content: 'Clean');

    expect($payload->getFullContent())->toBe('Clean');
});

it('adds and retrieves gate results', function () {
    $payload = new SentinelPayload(content: 'test');
    $result = new GateResult('my_gate', true, 'info', 'OK.');

    $payload->addResult($result);

    expect($payload->getResults())->toHaveKey('my_gate')
        ->and($payload->getResults()['my_gate'])->toBe($result);
});

it('converts to safeguard result with blocks', function () {
    $payload = new SentinelPayload(content: 'test');
    $payload->addResult(new GateResult('gate_a', false, 'block', 'Blocked!'));

    $safeguard = $payload->toSafeguardResult();

    expect($safeguard->passed)->toBeFalse()
        ->and($safeguard->blocks)->toContain('Blocked!')
        ->and($safeguard->warnings)->toBe([]);
});

it('converts to safeguard result with warnings', function () {
    $payload = new SentinelPayload(content: 'test');
    $payload->addResult(new GateResult('gate_a', false, 'warning', 'Watch out!'));

    $safeguard = $payload->toSafeguardResult();

    expect($safeguard->passed)->toBeTrue()
        ->and($safeguard->warnings)->toContain('Watch out!')
        ->and($safeguard->blocks)->toBe([]);
});

it('converts to a passing safeguard result', function () {
    $payload = new SentinelPayload(content: 'test');
    $payload->addResult(new GateResult('gate_a', true, 'info', 'OK.'));

    $safeguard = $payload->toSafeguardResult();

    expect($safeguard->passed)->toBeTrue()
        ->and($safeguard->warnings)->toBe([])
        ->and($safeguard->blocks)->toBe([]);
});
