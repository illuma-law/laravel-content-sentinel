<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel\Facades;

use IllumaLaw\ContentSentinel\ContentSentinel as BaseContentSentinel;
use Illuminate\Support\Facades\Facade;

/**
 * @see BaseContentSentinel
 */
class ContentSentinel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BaseContentSentinel::class;
    }
}
