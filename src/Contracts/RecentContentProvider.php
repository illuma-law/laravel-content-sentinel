<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel\Contracts;

use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;

interface RecentContentProvider
{
    /**
     * @return list<string>
     */
    public function getRecentContent(SentinelPayload $payload): array;
}
