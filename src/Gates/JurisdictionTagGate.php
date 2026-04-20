<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel\Gates;

use Closure;
use IllumaLaw\ContentSentinel\Contracts\SentinelGate;
use IllumaLaw\ContentSentinel\DTOs\GateResult;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;
use Illuminate\Support\Str;

class JurisdictionTagGate implements SentinelGate
{
    public function handle(SentinelPayload $payload, Closure $next): SentinelPayload
    {
        $locality = $payload->metadata['locality'] ?? null;
        $legalBasis = $payload->metadata['legal_basis'] ?? null;

        if ($locality && $legalBasis) {
            $localityLower = Str::lower((string) $locality);
            $basisLower = Str::lower((string) $legalBasis);

            if (! Str::contains($basisLower, $localityLower)) {
                $payload->addResult(new GateResult(
                    gate: 'jurisdiction_tag',
                    passed: false,
                    severity: 'warning',
                    message: "Topic locality \"{$locality}\" is not referenced in legal basis.",
                    metadata: ['locality' => $locality],
                ));

                return $next($payload);
            }
        }

        $payload->addResult(new GateResult(
            gate: 'jurisdiction_tag',
            passed: true,
            severity: 'info',
            message: 'Jurisdiction tag check passed or skipped.',
        ));

        return $next($payload);
    }
}
