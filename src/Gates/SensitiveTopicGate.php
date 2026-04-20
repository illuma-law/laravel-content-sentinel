<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel\Gates;

use Closure;
use IllumaLaw\ContentSentinel\Contracts\SentinelGate;
use IllumaLaw\ContentSentinel\DTOs\GateResult;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;
use Illuminate\Support\Str;

class SensitiveTopicGate implements SentinelGate
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(private readonly array $config = []) {}

    public function handle(SentinelPayload $payload, Closure $next): SentinelPayload
    {
        $topics = $this->config['sensitive_topics'] ?? [];
        $content = Str::lower($payload->getFullContent());

        $matched = [];

        foreach ($topics as $topic) {
            if (Str::contains($content, Str::lower($topic))) {
                $matched[] = $topic;
            }
        }

        if ($matched !== []) {
            $payload->addResult(new GateResult(
                gate: 'sensitive_topic',
                passed: false,
                severity: 'warning',
                message: 'Content touches sensitive topics: '.implode(', ', $matched),
                metadata: ['matched_topics' => $matched],
            ));
        } else {
            $payload->addResult(new GateResult(
                gate: 'sensitive_topic',
                passed: true,
                severity: 'info',
                message: 'No sensitive topics detected.',
            ));
        }

        return $next($payload);
    }
}
