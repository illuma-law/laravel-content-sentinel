<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel\DTOs;

class SafeguardResult
{
    /**
     * @param  list<string>  $warnings
     * @param  list<string>  $blocks
     * @param  array<string, GateResult>  $gateResults
     */
    public function __construct(
        public readonly bool $passed,
        public readonly array $warnings,
        public readonly array $blocks,
        public readonly array $gateResults,
    ) {}

    public function hasBlocks(): bool
    {
        return $this->blocks !== [];
    }

    public function hasWarnings(): bool
    {
        return $this->warnings !== [];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'passed' => $this->passed,
            'warnings' => $this->warnings,
            'blocks' => $this->blocks,
            'gate_results' => array_map(fn (GateResult $gate): array => $gate->toArray(), $this->gateResults),
        ];
    }
}
