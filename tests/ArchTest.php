<?php

declare(strict_types=1);

arch('source classes use strict types')
    ->expect('IllumaLaw\ContentSentinel')
    ->toUseStrictTypes();

arch('gates implement SentinelGate contract')
    ->expect('IllumaLaw\ContentSentinel\Gates')
    ->toImplement('IllumaLaw\ContentSentinel\Contracts\SentinelGate');

arch('DTOs are final or readonly-property classes')
    ->expect('IllumaLaw\ContentSentinel\DTOs')
    ->classes()
    ->not->toBeAbstract();

arch('contracts are interfaces')
    ->expect('IllumaLaw\ContentSentinel\Contracts')
    ->toBeInterfaces();

arch('facades extend Illuminate Facade')
    ->expect('IllumaLaw\ContentSentinel\Facades')
    ->toExtend('Illuminate\Support\Facades\Facade');

arch('service provider extends PackageServiceProvider')
    ->expect('IllumaLaw\ContentSentinel\ContentSentinelServiceProvider')
    ->toExtend('Spatie\LaravelPackageTools\PackageServiceProvider');
