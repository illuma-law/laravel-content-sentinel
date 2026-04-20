<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel;

use IllumaLaw\ContentSentinel\DTOs\SafeguardResult;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;
use Illuminate\Pipeline\Pipeline;

class ContentSentinel
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config,
        private readonly Pipeline $pipeline,
    ) {}

    public function check(SentinelPayload $payload): SafeguardResult
    {
        /** @var array<int, string> $gates */
        $gates = $this->config['gates'] ?? [];

        $resolvedGates = array_map(function (string $gateClass) {
            return app()->makeWith($gateClass, ['config' => $this->config]);
        }, $gates);

        $resultPayload = $this->pipeline
            ->send($payload)
            ->through($resolvedGates)
            ->thenReturn();

        assert($resultPayload instanceof SentinelPayload);

        return $resultPayload->toSafeguardResult();
    }
}
