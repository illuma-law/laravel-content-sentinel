# Laravel Content Sentinel

[![Tests](https://github.com/illuma-law/laravel-content-sentinel/actions/workflows/run-tests.yml/badge.svg)](https://github.com/illuma-law/laravel-content-sentinel/actions)
[![Packagist License](https://img.shields.io/badge/Licence-MIT-blue)](http://choosealicense.com/licenses/mit/)
[![Latest Stable Version](https://img.shields.io/packagist/v/illuma-law/laravel-content-sentinel?label=Version)](https://packagist.org/packages/illuma-law/laravel-content-sentinel)

**A configurable content safeguard and moderation pipeline for Laravel.**

This package provides a Pipeline-based architecture for running content through a sequence of configurable "gates" before it is processed.

- [Built-in Gates](#built-in-gates)
- [Installation](#installation)
- [Usage](#usage)
  - [Basic Usage](#basic-usage)
  - [Inspecting Results](#inspecting-results)
  - [Custom Gates](#custom-gates)
- [Testing](#testing)
- [Credits](#credits)
- [License](#license)

## Built-in Gates

| Gate | Key | Severity | Description |
| :--- | :--- | :--- | :--- |
| `ProhibitedPhrasesGate` | `prohibited_phrases` | **block** | Blocks content containing any configured prohibited phrase. |
| `BrandVoiceGate` | `brand_voice` | warning | Warns when content contains brand-forbidden words. |
| `DuplicateContentGate` | `duplicate_content` | warning | Warns when content similarity to recent content exceeds the threshold. |
| `SensitiveTopicGate` | `sensitive_topic` | warning | Warns when content touches any configured sensitive topic. |
| `HallucinationGate` | `hallucination` | warning | Warns when claims in the metadata cannot be verified. |

## Installation

You can install the package via composer:

```bash
composer require illuma-law/laravel-content-sentinel
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="content-sentinel-config"
```

## Usage

### TL;DR

```php
use IllumaLaw\ContentSentinel\Facades\ContentSentinel;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;

$payload = new SentinelPayload(content: 'My content...');
$result = ContentSentinel::check($payload);

if ($result->hasBlocks()) {
    // Handle failure
}
```

### Basic Usage

```php
use IllumaLaw\ContentSentinel\ContentSentinel;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;

$payload = new SentinelPayload(
    content: 'My article body...',
    title: 'Headline',
    caption: 'Social media caption',
    metadata: [
        'claims' => ['Claim 1', 'Claim 2'],
    ],
);

$sentinel = app(ContentSentinel::class);
$result = $sentinel->check($payload);
```

### Inspecting Results

```php
if ($result->hasBlocks()) {
    return response()->json(['errors' => $result->blocks], 422);
}

if ($result->hasWarnings()) {
    logger()->warning('Content flagged for review', $result->toArray());
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
        // ... logic ...
        return $next($payload);
    }
}
```

## Testing

The package includes a comprehensive test suite using Pest.

```bash
composer test
```

## Credits

- [illuma-law](https://github.com/illuma-law)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
