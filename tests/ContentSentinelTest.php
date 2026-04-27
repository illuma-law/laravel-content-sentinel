<?php

declare(strict_types=1);

use IllumaLaw\ContentSentinel\ContentSentinel;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;
use IllumaLaw\ContentSentinel\Gates\BrandVoiceGate;
use IllumaLaw\ContentSentinel\Gates\ProhibitedPhrasesGate;
use Illuminate\Pipeline\Pipeline;

it('runs the full pipeline', function () {
    $config = [
        'gates' => [
            ProhibitedPhrasesGate::class,
            BrandVoiceGate::class,
        ],
        'prohibited_phrases' => ['guaranteed outcome'],
        'brand_forbidden_words' => ['cheap'],
    ];

    $pipeline = new Pipeline(app());
    $sentinel = new ContentSentinel($config, $pipeline);

    $payload = new SentinelPayload(
        content: 'This is a cheap way to get a guaranteed outcome.'
    );

    $result = $sentinel->check($payload);

    expect($result->passed)->toBeFalse()
        ->and($result->blocks)->toHaveCount(1)
        ->and($result->warnings)->toHaveCount(1)
        ->and($result->gateResults)->toHaveKeys(['prohibited_phrases', 'brand_voice']);
});
