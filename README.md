# Laravel Content Sentinel

[![Tests](https://github.com/illuma-law/laravel-content-sentinel/actions/workflows/run-tests.yml/badge.svg)](https://github.com/illuma-law/laravel-content-sentinel/actions)
[![Packagist License](https://img.shields.io/badge/Licence-MIT-blue)](http://choosealicense.com/licenses/mit/)
[![Latest Stable Version](https://img.shields.io/packagist/v/illuma-law/laravel-content-sentinel?label=Version)](https://packagist.org/packages/illuma-law/laravel-content-sentinel)

**A configurable content safeguard and moderation pipeline for Laravel.**

This package provides a clean, extensible Pipeline-based architecture for running content through a sequence of configurable "gates" before it is published or processed. Each gate performs a single check (e.g., detecting prohibited phrases, sensitive topics, or duplicate content) and records a typed result. The final `SafeguardResult` aggregates all blocks and warnings for your application to act on.

- [Built-in Gates](#built-in-gates)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Basic Usage](#basic-usage)
  - [Inspecting Results](#inspecting-results)
  - [Custom Gates](#custom-gates)
  - [External Service Implementations](#external-service-implementations)
- [Testing](#testing)
- [Credits](#credits)
- [License](#license)

## Built-in Gates

| Gate | Key | Severity | Description |
| :--- | :--- | :--- | :--- |
| `ProhibitedAdviceGate` | `prohibited_advice` | **block** | Blocks content containing any configured prohibited phrase. |
| `BrandVoiceGate` | `brand_voice` | warning | Warns when content contains brand-forbidden words. |
| `DuplicateContentGate` | `duplicate_content` | warning | Warns when content similarity to recent content exceeds the threshold. Requires a `RecentContentProvider`. |
| `SensitiveTopicGate` | `sensitive_topic` | warning | Warns when content touches any configured sensitive topic. |
| `JurisdictionTagGate` | `jurisdiction_tag` | warning | Warns when the `locality` metadata key is not referenced in the `legal_basis` metadata key. |
| `HallucinationGate` | `hallucination` | warning | Warns when claims in the metadata cannot be verified. Requires a `FactChecker`. |

## Installation

Require this package with composer:

```bash
composer require illuma-law/laravel-content-sentinel
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="content-sentinel-config"
```

This will publish `config/content-sentinel.php`. The key options are:

| Key | Type | Description |
| :--- | :--- | :--- |
| `gates` | `array` | Ordered list of gate class names to execute. |
| `prohibited_phrases` | `array` | Phrases that trigger a **block**. |
| `brand_forbidden_words` | `array` | Words that trigger a **warning**. |
| `sensitive_topics` | `array` | Topics that trigger a **warning**. |
| `duplicate_similarity_threshold` | `float` | Similarity ratio (0–1) above which content is flagged as duplicate. Default `0.85`. |
| `hallucination_check_enabled` | `bool` | Toggle claim verification. Default `true`. |
| `fact_checker` | `?string` | FQCN of your `FactChecker` implementation. |
| `recent_content_provider` | `?string` | FQCN of your `RecentContentProvider` implementation. |

## Usage

### Basic Usage

```php
use IllumaLaw\ContentSentinel\ContentSentinel;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;

$payload = new SentinelPayload(
    content: 'My article body...',
    title: 'Headline',
    caption: 'Social media caption',
    metadata: [
        'locality'    => 'New York',
        'legal_basis' => 'New York Penal Law',
        'claims'      => ['Claim 1', 'Claim 2'],
    ],
);

$sentinel = app(ContentSentinel::class);
$result   = $sentinel->check($payload);
```

Or use the facade:

```php
use IllumaLaw\ContentSentinel\Facades\ContentSentinel;

$result = ContentSentinel::check($payload);
```

### Inspecting Results

```php
if ($result->hasBlocks()) {
    // Hard failure — prevent the action (e.g., publishing)
    return response()->json(['errors' => $result->blocks], 422);
}

if ($result->hasWarnings()) {
    // Soft failure — flag for review
    logger()->warning('Content flagged for review', $result->toArray());
}

// Inspect individual gate outcomes
foreach ($result->gateResults as $key => $gateResult) {
    echo "{$key}: " . ($gateResult->passed ? 'PASS' : 'FAIL') . PHP_EOL;
}
```

### Custom Gates

Implement the `SentinelGate` interface and add the class to your `gates` config array:

```php
namespace App\Gates;

use Closure;
use IllumaLaw\ContentSentinel\Contracts\SentinelGate;
use IllumaLaw\ContentSentinel\DTOs\GateResult;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;

class ProfanityGate implements SentinelGate
{
    public function __construct(private readonly array $config = []) {}

    public function handle(SentinelPayload $payload, Closure $next): SentinelPayload
    {
        $words = $this->config['profanity_list'] ?? [];

        foreach ($words as $word) {
            if (str_contains(strtolower($payload->content), strtolower($word))) {
                $payload->addResult(new GateResult(
                    gate: 'profanity',
                    passed: false,
                    severity: 'block',
                    message: "Content contains prohibited word: {$word}",
                ));

                return $next($payload);
            }
        }

        $payload->addResult(new GateResult(
            gate: 'profanity',
            passed: true,
            severity: 'info',
            message: 'No profanity detected.',
        ));

        return $next($payload);
    }
}
```

Then in `config/content-sentinel.php`:

```php
'gates' => [
    \App\Gates\ProfanityGate::class,
    // ...
],
```

### External Service Implementations

The `DuplicateContentGate` and `HallucinationGate` delegate to application-provided implementations.

#### FactChecker

Implement the `FactChecker` contract and register it in config:

```php
namespace App\Services;

use IllumaLaw\ContentSentinel\Contracts\FactChecker;

class VectorFactChecker implements FactChecker
{
    public function verifyClaim(string $claim): bool
    {
        // Perform a vector similarity search against your knowledge base
        return true;
    }
}
```

In `config/content-sentinel.php`:

```php
'fact_checker' => \App\Services\VectorFactChecker::class,
```

#### RecentContentProvider

```php
namespace App\Services;

use App\Models\Post;
use IllumaLaw\ContentSentinel\Contracts\RecentContentProvider;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;

class DbRecentContentProvider implements RecentContentProvider
{
    public function getRecentContent(SentinelPayload $payload): array
    {
        return Post::latest()->take(20)->pluck('body')->all();
    }
}
```

In `config/content-sentinel.php`:

```php
'recent_content_provider' => \App\Services\DbRecentContentProvider::class,
```

## Testing

The package includes a comprehensive Pest test suite with arch tests.

```bash
composer test
```

## Credits

- [illuma-law](https://github.com/illuma-law)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
