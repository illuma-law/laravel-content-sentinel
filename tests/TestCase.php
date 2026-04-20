<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel\Tests;

use IllumaLaw\ContentSentinel\ContentSentinelServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            ContentSentinelServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('content-sentinel.prohibited_phrases', [
            'guaranteed outcome',
            'we will win your case',
        ]);

        $app['config']->set('content-sentinel.brand_forbidden_words', [
            'cheap',
            'ambulance chaser',
        ]);

        $app['config']->set('content-sentinel.sensitive_topics', [
            'minors',
            'criminal law',
        ]);

        $app['config']->set('content-sentinel.duplicate_similarity_threshold', 0.85);

        $app['config']->set('content-sentinel.hallucination_check_enabled', true);
    }
}
