# Laravel Content Sentinel

[![Tests](https://github.com/illuma-law/laravel-content-sentinel/actions/workflows/run-tests.yml/badge.svg)](https://github.com/illuma-law/laravel-content-sentinel/actions)
[![Packagist License](https://img.shields.io/badge/Licence-MIT-blue)](http://choosealicense.com/licenses/mit/)
[![Latest Stable Version](https://img.shields.io/packagist/v/illuma-law/laravel-content-sentinel?label=Version)](https://packagist.org/packages/illuma-law/laravel-content-sentinel)

A configurable content safeguard and moderation pipeline for Laravel applications.

Content Sentinel acts as an automated editor for user-generated content or AI-generated text. It uses a Pipeline architecture to run content through a sequence of customizable "gates" before it is processed or published. These gates can either block the content entirely (throwing a hard error) or attach warnings (flagging the content for manual review).

## Features

- **Pipeline Architecture:** Run your content through multiple independent checks sequentially.
- **Severity Levels:** Differentiates between hard blocks (e.g., prohibited phrases) and warnings (e.g., sensitive topics).
- **Built-in Gates:** Includes common checks for prohibited phrases, brand voice adherence, and duplicate content.
- **Extensible:** Easily create custom gates tailored to your business logic.

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

Publish the config file to customize which gates run and how they behave:

```bash
php artisan vendor:publish --tag="content-sentinel-config"
```

## Configuration

The published `config/content-sentinel.php` file allows you to define your pipeline and the settings for each gate:

```php
return [
    // Register the gates you want to run, in order
    'gates' => [
        \IllumaLaw\ContentSentinel\Gates\ProhibitedPhrasesGate::class,
        \IllumaLaw\ContentSentinel\Gates\BrandVoiceGate::class,
        \IllumaLaw\ContentSentinel\Gates\SensitiveTopicGate::class,
    ],

    // Specific configuration for the ProhibitedPhrasesGate
    'prohibited_phrases' => [
        'swear_words',
        'competitor_name',
    ],
    
    // Configuration for the BrandVoiceGate
    'brand_voice_violations' => [
        'cheap',
        'guaranteed',
    ],
];
```

## Usage & Integration

The package works by creating a `SentinelPayload` object, running it through the `ContentSentinel` pipeline, and inspecting the resulting `SafeguardResult`.

### Using the Facade

You can use the Facade to quickly evaluate a payload:

```php
use IllumaLaw\ContentSentinel\Facades\ContentSentinel;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;

$payload = new SentinelPayload(
    content: 'Our new product is cheap and guaranteed to work!',
    title: 'Product Launch',
    caption: 'Buy now!',
    metadata: ['author_id' => 123]
);

$result = ContentSentinel::check($payload);

if ($result->hasBlocks()) {
    // A block gate failed. Prevent the action.
    abort(422, 'Content violates policies: ' . json_encode($result->blocks));
}

if ($result->hasWarnings()) {
    // A warning gate failed. Allow it, but flag it for review.
    logger()->warning('Content flagged for review', $result->warnings);
}

// Proceed to save/publish...
```

### Dependency Injection

Alternatively, you can resolve the `ContentSentinel` service from the container:

```php
use IllumaLaw\ContentSentinel\ContentSentinel;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;

class ContentController extends Controller
{
    public function store(Request $request, ContentSentinel $sentinel)
    {
        $payload = new SentinelPayload(content: $request->input('body'));
        $result = $sentinel->check($payload);
        
        if ($result->hasBlocks()) {
            return back()->withErrors(['body' => 'Contains prohibited content.']);
        }
        
        // ...
    }
}
```

### Creating Custom Gates

To create your own gate, implement the `SentinelGate` interface. The gate receives the payload and the configuration array. 

Use the `$payload->addResult()` method to append your check's status.

```php
namespace App\Gates;

use Closure;
use IllumaLaw\ContentSentinel\Contracts\SentinelGate;
use IllumaLaw\ContentSentinel\DTOs\GateResult;
use IllumaLaw\ContentSentinel\DTOs\SentinelPayload;
use IllumaLaw\ContentSentinel\Enums\Severity;

class ProfanityGate implements SentinelGate
{
    public function __construct(private readonly array $config = []) {}

    public function handle(SentinelPayload $payload, Closure $next): SentinelPayload
    {
        if (str_contains(strtolower($payload->content), 'badword')) {
            $payload->addResult(new GateResult(
                gateKey: 'profanity_check',
                passed: false,
                severity: Severity::BLOCK, // or Severity::WARNING
                message: 'Content contains profanity.',
                details: ['word' => 'badword']
            ));
        } else {
             $payload->addResult(new GateResult(
                gateKey: 'profanity_check',
                passed: true,
                severity: Severity::INFO,
                message: 'No profanity detected.'
            ));
        }

        return $next($payload);
    }
}
```

Register your custom gate in the `config/content-sentinel.php` file:

```php
    'gates' => [
        \App\Gates\ProfanityGate::class,
        // ...
    ],
```

## Testing

Run the test suite:

```shell
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.