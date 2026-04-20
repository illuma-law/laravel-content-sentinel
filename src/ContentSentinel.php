<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel;

use IllumaLaw\ContentSentinel\DTOs\SafeguardResult;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;
use IllumaLaw\ContentSentinel\Events\ContentApproved;
use IllumaLaw\ContentSentinel\Events\ContentFlagged;
use IllumaLaw\ContentSentinel\Events\ContentRejected;
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

        $result = $resultPayload->toSafeguardResult();

        $this->dispatchEvents($payload, $result);

        return $result;
    }

    private function dispatchEvents(SentinelPayload $payload, SafeguardResult $result): void
    {
        if ($result->hasBlocks()) {
            ContentRejected::dispatch($payload, $result);

            return;
        }

        if ($result->hasWarnings()) {
            ContentFlagged::dispatch($payload, $result);

            return;
        }

        ContentApproved::dispatch($payload, $result);
    }
}
