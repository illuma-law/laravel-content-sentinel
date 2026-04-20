<?php

declare(strict_types=1);

use IllumaLaw\ContentSentinel\Contracts\FactChecker;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;
use IllumaLaw\ContentSentinel\Gates\HallucinationGate;

it('warns when claims cannot be verified', function () {
    $factChecker = Mockery::mock(FactChecker::class);
    $factChecker->shouldReceive('verifyClaim')->with('Unverified claim')->andReturn(false);

    $payload = new SentinelPayload(
        content: 'test',
        metadata: ['claims' => ['Unverified claim']]
    );

    $config = ['hallucination_check_enabled' => true];
    $gate = new HallucinationGate($config, $factChecker);
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    $result = $resultPayload->getResults()['hallucination'];
    expect($result->passed)->toBeFalse()
        ->and($result->message)->toContain('Some claims could not be verified')
        ->and($result->message)->not->toContain('legal corpus');
});

it('passes when all claims are verified', function () {
    $factChecker = Mockery::mock(FactChecker::class);
    $factChecker->shouldReceive('verifyClaim')->with('Verified claim')->andReturn(true);

    $payload = new SentinelPayload(
        content: 'test',
        metadata: ['claims' => ['Verified claim']]
    );

    $config = ['hallucination_check_enabled' => true];
    $gate = new HallucinationGate($config, $factChecker);
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    $result = $resultPayload->getResults()['hallucination'];
    expect($result->passed)->toBeTrue();
});

it('skips when no claims are present', function () {
    $factChecker = Mockery::mock(FactChecker::class);
    $payload = new SentinelPayload(content: 'test');

    $config = ['hallucination_check_enabled' => true];
    $gate = new HallucinationGate($config, $factChecker);
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    $result = $resultPayload->getResults()['hallucination'];
    expect($result->passed)->toBeTrue()
        ->and($result->message)->toBe('No claims to verify.');
});

it('skips entirely when hallucination check is disabled', function () {
    $payload = new SentinelPayload(
        content: 'test',
        metadata: ['claims' => ['A claim']],
    );

    $config = ['hallucination_check_enabled' => false];
    $gate = new HallucinationGate($config, null);
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    expect($resultPayload->getResults())->not->toHaveKey('hallucination');
});

it('skips entirely when no fact checker is provided', function () {
    $payload = new SentinelPayload(
        content: 'test',
        metadata: ['claims' => ['A claim']],
    );

    $config = ['hallucination_check_enabled' => true];
    $gate = new HallucinationGate($config, null);
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    expect($resultPayload->getResults())->not->toHaveKey('hallucination');
});
