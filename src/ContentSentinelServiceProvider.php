<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel;

use IllumaLaw\ContentSentinel\Contracts\FactChecker;
use IllumaLaw\ContentSentinel\Contracts\RecentContentProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Pipeline\Pipeline;
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
        $this->app->singleton(ContentSentinel::class, function (Application $app) {
            /** @var Repository $config */
            $config = $app->make('config');

            /** @var Pipeline $pipeline */
            $pipeline = $app->make('pipeline');

            /** @var array<string, mixed> $sentinelConfig */
            $sentinelConfig = $config->get('content-sentinel', []);

            return new ContentSentinel(
                config: $sentinelConfig,
                pipeline: $pipeline,
            );
        });
    }

    public function packageRegistered(): void
    {
        /** @var Repository $configRepository */
        $configRepository = $this->app->make('config');

        /** @var array<string, mixed> $config */
        $config = (array) $configRepository->get('content-sentinel', []);

        if ($factChecker = ($config['fact_checker'] ?? null)) {
            /** @var class-string|(\Closure(Application): mixed)|null $factChecker */
            $this->app->bind(FactChecker::class, $factChecker);
        }

        if ($recentContentProvider = ($config['recent_content_provider'] ?? null)) {
            /** @var class-string|(\Closure(Application): mixed)|null $recentContentProvider */
            $this->app->bind(RecentContentProvider::class, $recentContentProvider);
        }
    }
}
