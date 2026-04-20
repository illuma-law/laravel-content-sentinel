<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel\Gates;

use Closure;
use IllumaLaw\ContentSentinel\Contracts\SentinelGate;
use IllumaLaw\ContentSentinel\DTOs\GateResult;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;
use Illuminate\Support\Str;

class ProhibitedAdviceGate implements SentinelGate
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(private readonly array $config = []) {}

    public function handle(SentinelPayload $payload, Closure $next): SentinelPayload
    {
        $phrases = $this->config['prohibited_phrases'] ?? [];
        $content = Str::lower($payload->getFullContent());

        $matched = [];

        foreach ($phrases as $phrase) {
            if (Str::contains($content, Str::lower($phrase))) {
                $matched[] = $phrase;
            }
        }

        if ($matched !== []) {
            $payload->addResult(new GateResult(
                gate: 'prohibited_advice',
                passed: false,
                severity: 'block',
                message: 'Content contains prohibited phrases: '.implode(', ', $matched),
                metadata: ['matched_phrases' => $matched],
            ));
        } else {
            $payload->addResult(new GateResult(
                gate: 'prohibited_advice',
                passed: true,
                severity: 'info',
                message: 'No prohibited advice phrases detected.',
            ));
        }

        return $next($payload);
    }
}
