# illuma-law/laravel-content-sentinel

Automated editor and moderation pipeline for user-generated or AI-generated text using a Pipeline architecture.

## Key Concepts

- **SentinelPayload**: DTO containing `content`, `title`, `caption`, and `metadata`.
- **SafeguardResult**: Result object containing `blocks` (hard errors) and `warnings`.
- **Severity**: Enum with `BLOCK`, `WARNING`, and `INFO`.

## Usage

```php
use IllumaLaw\ContentSentinel\Facades\ContentSentinel;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;

$payload = new SentinelPayload(content: 'Some user content...');
$result = ContentSentinel::check($payload);

if ($result->hasBlocks()) {
    // Prevent action
}

if ($result->hasWarnings()) {
    // Flag for review
}
```

## Built-in Gates

- `ProhibitedPhrasesGate`: Blocks specific phrases.
- `BrandVoiceGate`: Warns about voice violations.
- `DuplicateContentGate`: Warns if similar to recent content.
- `SensitiveTopicGate`: Warns about sensitive topics.

## Custom Gates

Implement `SentinelGate` interface:

```php
public function handle(SentinelPayload $payload, Closure $next): SentinelPayload
{
    if ($condition) {
        $payload->addResult(new GateResult(
            gateKey: 'my_gate',
            passed: false,
            severity: Severity::BLOCK,
            message: 'Error message'
        ));
    }
    return $next($payload);
}
```

## Configuration

Publish config: `php artisan vendor:publish --tag="content-sentinel-config"`

Define pipeline in `config/content-sentinel.php`:
```php
'gates' => [
    \IllumaLaw\ContentSentinel\Gates\ProhibitedPhrasesGate::class,
    // ...
],
```
