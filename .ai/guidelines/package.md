---
description: Configurable content moderation pipeline for Laravel — gate-based, hard blocks + warnings
---

# laravel-content-sentinel

Configurable content safeguard and moderation pipeline. Runs content through a sequence of gates that either block (hard error) or warn (flag for review).

## Namespace

`IllumaLaw\ContentSentinel`

## Key Classes

- `ContentSentinel` — main service, injectable
- `SentinelPayload` DTO — wraps content + metadata for pipeline input
- `ContentModerated` event — fired after moderation completes

## Built-in Gates

| Gate | Key | Severity |
|---|---|---|
| `ProhibitedPhrasesGate` | `prohibited_phrases` | block |
| `BrandVoiceGate` | `brand_voice` | warning |
| `DuplicateContentGate` | `duplicate_content` | warning |
| `SensitiveTopicGate` | `sensitive_topic` | warning |
| `HallucinationGate` | `hallucination` | warning |

## Usage

```php
use IllumaLaw\ContentSentinel\ContentSentinel;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;

$payload = new SentinelPayload(
    content: $text,
    metadata: ['platform' => 'linkedin', 'author_id' => $userId],
);

$result = app(ContentSentinel::class)->moderate($payload);

if ($result->isBlocked()) {
    // Hard stop — do not publish
}

foreach ($result->warnings() as $warning) {
    // Flag for human review
}
```

## Custom Gate

```php
use IllumaLaw\ContentSentinel\Contracts\SentinelGate;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;
use IllumaLaw\ContentSentinel\DTOs\GateResult;

class MyCustomGate implements SentinelGate
{
    public function check(SentinelPayload $payload): GateResult
    {
        return GateResult::pass();
        // or GateResult::warn('Reason');
        // or GateResult::block('Reason');
    }
}
```

## Config

Publish: `php artisan vendor:publish --tag="content-sentinel-config"`

Register gates in order in `config/content-sentinel.php`:
```php
'gates' => [
    \IllumaLaw\ContentSentinel\Gates\ProhibitedPhrasesGate::class,
    MyCustomGate::class,
],
```
