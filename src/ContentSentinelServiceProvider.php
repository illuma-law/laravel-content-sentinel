<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel;

use IllumaLaw\ContentSentinel\Contracts\FactChecker;
use IllumaLaw\ContentSentinel\Contracts\RecentContentProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ContentSentinelServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-content-sentinel')
            ->hasConfigFile();
    }

    public function registeringPackage(): void
    {
        $this->app->singleton(ContentSentinel::class, function ($app) {
            return new ContentSentinel(
                config: $app['config']->get('content-sentinel', []),
                pipeline: $app->make('pipeline'),
            );
        });
    }

    public function packageRegistered(): void
    {
        $config = $this->app['config']->get('content-sentinel', []);

        if ($factChecker = ($config['fact_checker'] ?? null)) {
            $this->app->bind(FactChecker::class, $factChecker);
        }

        if ($recentContentProvider = ($config['recent_content_provider'] ?? null)) {
            $this->app->bind(RecentContentProvider::class, $recentContentProvider);
        }
    }
}
