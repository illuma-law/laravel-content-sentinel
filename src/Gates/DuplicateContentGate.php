<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel\Gates;

use Closure;
use IllumaLaw\ContentSentinel\Contracts\RecentContentProvider;
use IllumaLaw\ContentSentinel\Contracts\SentinelGate;
use IllumaLaw\ContentSentinel\DTOs\GateResult;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;
use Illuminate\Support\Str;

class DuplicateContentGate implements SentinelGate
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config = [],
        private readonly ?RecentContentProvider $provider = null,
    ) {}

    public function handle(SentinelPayload $payload, Closure $next): SentinelPayload
    {
        if (! $this->provider) {
            return $next($payload);
        }

        $recentContent = $this->provider->getRecentContent($payload);
        $content = Str::lower($payload->content);
        $threshold = $this->config['duplicate_similarity_threshold'] ?? 0.85;

        foreach ($recentContent as $existingContent) {
            $similarity = 0.0;
            similar_text($content, Str::lower((string) $existingContent), $similarity);

            if ($similarity / 100 >= $threshold) {
                $payload->addResult(new GateResult(
                    gate: 'duplicate_content',
                    passed: false,
                    severity: 'warning',
                    message: sprintf('Content is %.0f%% similar to recent content.', $similarity),
                    metadata: ['similarity_percent' => round($similarity, 2)],
                ));

                return $next($payload);
            }
        }

        $payload->addResult(new GateResult(
            gate: 'duplicate_content',
            passed: true,
            severity: 'info',
            message: 'No duplicate content detected.',
        ));

        return $next($payload);
    }
}
