<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel\Gates;

use Closure;
use IllumaLaw\ContentSentinel\Contracts\FactChecker;
use IllumaLaw\ContentSentinel\Contracts\SentinelGate;
use IllumaLaw\ContentSentinel\DTOs\GateResult;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;

class HallucinationGate implements SentinelGate
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config = [],
        private readonly ?FactChecker $factChecker = null,
    ) {}

    public function handle(SentinelPayload $payload, Closure $next): SentinelPayload
    {
        $enabled = $this->config['hallucination_check_enabled'] ?? true;

        if (! $enabled || ! $this->factChecker) {
            return $next($payload);
        }

        /** @var list<string> $claims */
        $claims = $payload->metadata['claims'] ?? [];

        if ($claims === []) {
            $payload->addResult(new GateResult(
                gate: 'hallucination',
                passed: true,
                severity: 'info',
                message: 'No claims to verify.',
            ));

            return $next($payload);
        }

        $unverified = [];

        foreach ($claims as $claim) {
            if ($claim === '') {
                continue;
            }

            if (! $this->factChecker->verifyClaim($claim)) {
                $unverified[] = $claim;
            }
        }

        if ($unverified !== []) {
            $payload->addResult(new GateResult(
                gate: 'hallucination',
                passed: false,
                severity: 'warning',
                message: 'Some claims could not be verified: '.implode('; ', array_slice($unverified, 0, 3)),
                metadata: ['unverified_claims' => $unverified],
            ));
        } else {
            $payload->addResult(new GateResult(
                gate: 'hallucination',
                passed: true,
                severity: 'info',
                message: 'All claims verified.',
            ));
        }

        return $next($payload);
    }
}
