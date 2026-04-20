<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel\Gates;

use Closure;
use IllumaLaw\ContentSentinel\Contracts\SentinelGate;
use IllumaLaw\ContentSentinel\DTOs\GateResult;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;
use Illuminate\Support\Str;

class BrandVoiceGate implements SentinelGate
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(private readonly array $config = []) {}

    public function handle(SentinelPayload $payload, Closure $next): SentinelPayload
    {
        $forbidden = $this->config['brand_forbidden_words'] ?? [];
        $content = Str::lower($payload->getFullContent());

        $matched = [];

        foreach ($forbidden as $word) {
            if (Str::contains($content, Str::lower($word))) {
                $matched[] = $word;
            }
        }

        if ($matched !== []) {
            $payload->addResult(new GateResult(
                gate: 'brand_voice',
                passed: false,
                severity: 'warning',
                message: 'Content contains brand-forbidden words: '.implode(', ', $matched),
                metadata: ['matched_words' => $matched],
            ));
        } else {
            $payload->addResult(new GateResult(
                gate: 'brand_voice',
                passed: true,
                severity: 'info',
                message: 'Brand voice check passed.',
            ));
        }

        return $next($payload);
    }
}
