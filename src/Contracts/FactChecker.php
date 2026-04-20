<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel\Contracts;

interface FactChecker
{
    public function verifyClaim(string $claim): bool;
}
