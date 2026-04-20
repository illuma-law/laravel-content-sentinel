<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel\Support;

final class TextSanitizer
{
    public static function clean(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $text = self::stripMarkdownImages($text);
        $text = self::unwrapMarkdownLinks($text);
        $text = self::stripAngleBracketAutolinks($text);
        $text = self::stripExtendedPictographs($text);

        return $text;
    }

    public static function stripMarkdownImages(string $text): string
    {
        return preg_replace('/!\[([^\]]*)\]\([^)]*\)/u', '$1', $text) ?? $text;
    }

    public static function unwrapMarkdownLinks(string $text): string
    {
        $previous = null;

        while ($previous !== $text) {
            $previous = $text;
            $text = preg_replace('/\[([^\]]+)\]\(([^)]*)\)/u', '$1', $text) ?? $text;
        }

        return $text;
    }

    public static function stripAngleBracketAutolinks(string $text): string
    {
        return preg_replace_callback(
            '/<((?:https?|mailto):[^>\s]+)>/iu',
            static fn (array $m): string => $m[1] ?? '',
            $text,
        ) ?? $text;
    }

    public static function stripExtendedPictographs(string $text): string
    {
        $stripped = preg_replace('/\p{Extended_Pictographic}/u', '', $text);

        if ($stripped === null) {
            return $text;
        }

        $stripped = preg_replace('/[\x{FE0F}\x{FE0E}]/u', '', $stripped) ?? $stripped;

        return $stripped;
    }
}
