<?php

declare(strict_types=1);

use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;
use IllumaLaw\ContentSentinel\Gates\ProhibitedPhrasesGate;

it('blocks prohibited phrases', function () {
    $payload = new SentinelPayload(
        content: 'I can provide a prohibited phrase for your case.',
    );

    $config = ['prohibited_phrases' => ['prohibited phrase', 'other bad thing']];
    $gate = new ProhibitedPhrasesGate($config);
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    $result = $resultPayload->getResults()['prohibited_phrases'];
    expect($result->passed)->toBeFalse()
        ->and($result->severity)->toBe('block')
        ->and($result->message)->toContain('prohibited phrase');
});

it('passes when no prohibited phrases are present', function () {
    $payload = new SentinelPayload(
        content: 'I will help you with your generic content.',
    );

    $config = ['prohibited_phrases' => ['prohibited phrase', 'other bad thing']];
    $gate = new ProhibitedPhrasesGate($config);
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    $result = $resultPayload->getResults()['prohibited_phrases'];
    expect($result->passed)->toBeTrue()
        ->and($result->severity)->toBe('info');
});

it('handles empty content', function () {
    $payload = new SentinelPayload(content: '');

    $config = ['prohibited_phrases' => ['prohibited phrase', 'other bad thing']];
    $gate = new ProhibitedPhrasesGate($config);
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    $result = $resultPayload->getResults()['prohibited_phrases'];
    expect($result->passed)->toBeTrue();
});

it('is case insensitive', function () {
    $payload = new SentinelPayload(
        content: 'PROHIBITED PHRASE TODAY',
    );

    $config = ['prohibited_phrases' => ['prohibited phrase', 'other bad thing']];
    $gate = new ProhibitedPhrasesGate($config);
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    $result = $resultPayload->getResults()['prohibited_phrases'];
    expect($result->passed)->toBeFalse();
});
