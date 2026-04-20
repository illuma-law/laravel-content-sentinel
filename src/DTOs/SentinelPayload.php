<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel\DTOs;

class SentinelPayload
{
    /** @var array<string, GateResult> */
    private array $gateResults = [];

    /**
     * @param  string  $content  The main body content to check.
     * @param  string|null  $title  Optional title or headline.
     * @param  string|null  $caption  Optional caption or secondary content.
     * @param  array<string, mixed>  $metadata  Additional context like claims, locality, legal_basis, etc.
     */
    public function __construct(
        public readonly string $content,
        public readonly ?string $title = null,
        public readonly ?string $caption = null,
        public readonly array $metadata = [],
    ) {}

    public function addResult(GateResult $result): void
    {
        $this->gateResults[$result->gate] = $result;
    }

    /**
     * @return array<string, GateResult>
     */
    public function getResults(): array
    {
        return $this->gateResults;
    }

    public function toSafeguardResult(): SafeguardResult
    {
        $warnings = [];
        $blocks = [];

        foreach ($this->gateResults as $gate) {
            if (! $gate->passed && $gate->isBlock()) {
                $blocks[] = $gate->message;
            } elseif (! $gate->passed && $gate->isWarning()) {
                $warnings[] = $gate->message;
            }
        }

        return new SafeguardResult(
            passed: $blocks === [],
            warnings: $warnings,
            blocks: $blocks,
            gateResults: $this->gateResults,
        );
    }

    public function getFullContent(): string
    {
        return trim($this->content.' '.($this->title ?? '').' '.($this->caption ?? ''));
    }
}
