<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel\Contracts;

use Closure;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;

interface SentinelGate
{
    /**
     * @param  Closure(SentinelPayload): SentinelPayload  $next
     */
    public function handle(SentinelPayload $payload, Closure $next): SentinelPayload;
}
