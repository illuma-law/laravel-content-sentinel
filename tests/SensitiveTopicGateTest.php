<?php

declare(strict_types=1);

use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;
use IllumaLaw\ContentSentinel\Gates\SensitiveTopicGate;

it('warns about sensitive topics', function () {
    $payload = new SentinelPayload(
        content: 'Case involving minors in the court.',
    );

    $config = ['sensitive_topics' => ['minors']];
    $gate = new SensitiveTopicGate($config);
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    $result = $resultPayload->getResults()['sensitive_topic'];
    expect($result->passed)->toBeFalse()
        ->and($result->severity)->toBe('warning')
        ->and($result->message)->toContain('minors')
        ->and($result->message)->not->toContain('senior review');
});

it('passes when no sensitive topics are present', function () {
    $payload = new SentinelPayload(
        content: 'Commercial real estate law updates.',
    );

    $config = ['sensitive_topics' => ['minors']];
    $gate = new SensitiveTopicGate($config);
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    $result = $resultPayload->getResults()['sensitive_topic'];
    expect($result->passed)->toBeTrue();
});
