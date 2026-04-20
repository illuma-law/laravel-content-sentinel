<?php

declare(strict_types=1);

use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;
use IllumaLaw\ContentSentinel\Gates\JurisdictionTagGate;

it('warns when locality is missing from legal basis', function () {
    $payload = new SentinelPayload(
        content: 'test',
        metadata: [
            'locality' => 'Berlin',
            'legal_basis' => 'Laws of Paris',
        ]
    );

    $gate = new JurisdictionTagGate;
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    $result = $resultPayload->getResults()['jurisdiction_tag'];
    expect($result->passed)->toBeFalse()
        ->and($result->message)->toContain('Berlin');
});

it('passes when locality is in legal basis', function () {
    $payload = new SentinelPayload(
        content: 'test',
        metadata: [
            'locality' => 'Berlin',
            'legal_basis' => 'The Berlin City Charter',
        ]
    );

    $gate = new JurisdictionTagGate;
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    $result = $resultPayload->getResults()['jurisdiction_tag'];
    expect($result->passed)->toBeTrue();
});

it('skips when metadata is missing', function () {
    $payload = new SentinelPayload(content: 'test');

    $gate = new JurisdictionTagGate;
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    $result = $resultPayload->getResults()['jurisdiction_tag'];
    expect($result->passed)->toBeTrue();
});
