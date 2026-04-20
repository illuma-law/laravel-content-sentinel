<?php

declare(strict_types=1);

use IllumaLaw\ContentSentinel\Contracts\RecentContentProvider;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;
use IllumaLaw\ContentSentinel\Gates\DuplicateContentGate;
use Mockery\MockInterface;

it('warns when similarity is above threshold', function () {
    $existingContent = 'This is a very unique content that should be caught.';
    $newContent = 'This is very unique content that should be caught.';

    /** @var RecentContentProvider&MockInterface $provider */
    $provider = Mockery::mock(RecentContentProvider::class, [
        'getRecentContent' => [$existingContent],
    ]);

    $payload = new SentinelPayload(content: $newContent);

    $config = ['duplicate_similarity_threshold' => 0.85];
    $gate = new DuplicateContentGate($config, $provider);
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    $result = $resultPayload->getResults()['duplicate_content'];
    expect($result->passed)->toBeFalse()
        ->and($result->severity)->toBe('warning');
});

it('passes when similarity is below threshold', function () {
    $existingContent = 'Completely different topic about law.';
    $newContent = 'Something else entirely about marketing.';

    /** @var RecentContentProvider&MockInterface $provider */
    $provider = Mockery::mock(RecentContentProvider::class, [
        'getRecentContent' => [$existingContent],
    ]);

    $payload = new SentinelPayload(content: $newContent);

    $config = ['duplicate_similarity_threshold' => 0.85];
    $gate = new DuplicateContentGate($config, $provider);
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    $result = $resultPayload->getResults()['duplicate_content'];
    expect($result->passed)->toBeTrue();
});

it('skips when no provider is given', function () {
    $payload = new SentinelPayload(content: 'test');
    $gate = new DuplicateContentGate([], null);
    $resultPayload = $gate->handle($payload, fn ($p) => $p);

    expect($resultPayload->getResults())->not->toHaveKey('duplicate_content');
});
