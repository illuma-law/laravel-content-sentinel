<?php

declare(strict_types=1);

use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;
use IllumaLaw\ContentSentinel\Gates\BrandVoiceGate;

it('warns about brand-forbidden words', function () {
    $payload = new SentinelPayload(
        content: 'We are not an ambulance chaser firm.',
    );

    $config = ['brand_forbidden_words' => ['ambulance chaser']];
    $gate = new BrandVoiceGate($config);
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    $result = $resultPayload->getResults()['brand_voice'];
    expect($result->passed)->toBeFalse()
        ->and($result->severity)->toBe('warning')
        ->and($result->message)->toContain('ambulance chaser');
});

it('passes when no forbidden words are present', function () {
    $payload = new SentinelPayload(
        content: 'Professional legal services for everyone.',
    );

    $config = ['brand_forbidden_words' => ['ambulance chaser']];
    $gate = new BrandVoiceGate($config);
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    $result = $resultPayload->getResults()['brand_voice'];
    expect($result->passed)->toBeTrue();
});
