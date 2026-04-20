<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel\Events;

use IllumaLaw\ContentSentinel\DTOs\SafeguardResult;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class ContentModerated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly SentinelPayload $payload,
        public readonly SafeguardResult $result,
    ) {}
}
