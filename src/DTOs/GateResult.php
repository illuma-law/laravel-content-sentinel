<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel\DTOs;

class GateResult
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $gate,
        public readonly bool $passed,
        public readonly string $severity,
        public readonly string $message,
        public readonly array $metadata = [],
    ) {}

    public function isBlock(): bool
    {
        return $this->severity === 'block';
    }

    public function isWarning(): bool
    {
        return $this->severity === 'warning';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'gate' => $this->gate,
            'passed' => $this->passed,
            'severity' => $this->severity,
            'message' => $this->message,
            'metadata' => $this->metadata,
        ];
    }
}
