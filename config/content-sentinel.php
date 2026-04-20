<?php

declare(strict_types=1);
use IllumaLaw\ContentSentinel\Gates\BrandVoiceGate;
use IllumaLaw\ContentSentinel\Gates\DuplicateContentGate;
use IllumaLaw\ContentSentinel\Gates\HallucinationGate;
use IllumaLaw\ContentSentinel\Gates\JurisdictionTagGate;
use IllumaLaw\ContentSentinel\Gates\ProhibitedAdviceGate;
use IllumaLaw\ContentSentinel\Gates\SensitiveTopicGate;

return [
    /*
    |--------------------------------------------------------------------------
    | Content Sentinel Gates
    |--------------------------------------------------------------------------
    |
    | These classes implement the SentinelGate interface and are executed in
    | the order they are defined. You can add or remove gates as needed.
    |
    */
    'gates' => [
        ProhibitedAdviceGate::class,
        BrandVoiceGate::class,
        DuplicateContentGate::class,
        SensitiveTopicGate::class,
        JurisdictionTagGate::class,
        HallucinationGate::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Moderation Settings
    |--------------------------------------------------------------------------
    */

    'prohibited_phrases' => [
        // 'guaranteed outcome',
        // 'we will win',
        // '100% success',
    ],

    'brand_forbidden_words' => [
        // 'cheap',
        // 'free consultation',
    ],

    'sensitive_topics' => [
        // 'minors',
        // 'violence',
        // 'mental health',
    ],

    'duplicate_similarity_threshold' => 0.85,

    'hallucination_check_enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | External Service Implementations
    |--------------------------------------------------------------------------
    |
    | Define the classes that implement the FactChecker and RecentContentProvider
    | interfaces. These are usually defined in the host application.
    |
    */
    'fact_checker' => null, // e.g., App\Services\AppFactChecker::class

    'recent_content_provider' => null, // e.g., App\Services\DuplicateContentProvider::class
];
