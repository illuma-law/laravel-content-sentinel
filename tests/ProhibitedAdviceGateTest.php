<?php

declare(strict_types=1);

use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;
use IllumaLaw\ContentSentinel\Gates\ProhibitedAdviceGate;

it('blocks prohibited advice phrases', function () {
    $payload = new SentinelPayload(
        content: 'I can provide a guaranteed outcome for your case.',
    );

    $config = ['prohibited_phrases' => ['guaranteed outcome', 'we will win your case']];
    $gate = new ProhibitedAdviceGate($config);
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    $result = $resultPayload->getResults()['prohibited_advice'];
    expect($result->passed)->toBeFalse()
        ->and($result->severity)->toBe('block')
        ->and($result->message)->toContain('guaranteed outcome')
        ->and($result->message)->not->toContain('legal advice');
});

it('passes when no prohibited advice is present', function () {
    $payload = new SentinelPayload(
        content: 'I will help you with your legal documents.',
    );

    $config = ['prohibited_phrases' => ['guaranteed outcome', 'we will win your case']];
    $gate = new ProhibitedAdviceGate($config);
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    $result = $resultPayload->getResults()['prohibited_advice'];
    expect($result->passed)->toBeTrue()
        ->and($result->severity)->toBe('info');
});

it('handles empty content', function () {
    $payload = new SentinelPayload(content: '');

    $config = ['prohibited_phrases' => ['guaranteed outcome', 'we will win your case']];
    $gate = new ProhibitedAdviceGate($config);
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    $result = $resultPayload->getResults()['prohibited_advice'];
    expect($result->passed)->toBeTrue();
});

it('is case insensitive', function () {
    $payload = new SentinelPayload(
        content: 'WE WILL WIN YOUR CASE TODAY',
    );

    $config = ['prohibited_phrases' => ['guaranteed outcome', 'we will win your case']];
    $gate = new ProhibitedAdviceGate($config);
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    $result = $resultPayload->getResults()['prohibited_advice'];
    expect($result->passed)->toBeFalse();
});
