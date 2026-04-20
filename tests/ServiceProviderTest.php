<?php

declare(strict_types=1);

use IllumaLaw\ContentSentinel\ContentSentinel;
use IllumaLaw\ContentSentinel\Contracts\FactChecker;
use IllumaLaw\ContentSentinel\Contracts\RecentContentProvider;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;
use IllumaLaw\ContentSentinel\Facades\ContentSentinel as ContentSentinelFacade;

it('resolves ContentSentinel from the container', function () {
    expect(app(ContentSentinel::class))->toBeInstanceOf(ContentSentinel::class);
});

it('resolves ContentSentinel via the facade', function () {
    $payload = new SentinelPayload(content: 'safe content');
    $result = ContentSentinelFacade::check($payload);

    expect($result->passed)->toBeTrue();
});

it('binds FactChecker when configured', function () {
    $mock = new class implements FactChecker
    {
        public function verifyClaim(string $claim): bool
        {
            return true;
        }
    };

    app('config')->set('content-sentinel.fact_checker', $mock::class);

    app()->bind(FactChecker::class, $mock::class);

    expect(app(FactChecker::class))->toBeInstanceOf(FactChecker::class);
});

it('binds RecentContentProvider when configured', function () {
    $mock = new class implements RecentContentProvider
    {
        public function getRecentContent(SentinelPayload $payload): array
        {
            return [];
        }
    };

    app()->bind(RecentContentProvider::class, $mock::class);

    expect(app(RecentContentProvider::class))->toBeInstanceOf(RecentContentProvider::class);
});
