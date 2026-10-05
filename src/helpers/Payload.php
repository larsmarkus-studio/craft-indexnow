<?php

declare(strict_types=1);

namespace larsmarkusstudio\indexnow\helpers;

/**
 * Pure helpers (no Craft), so they can be tested with `php tests/payload.php`.
 */
class Payload
{
    /** The IndexNow limit per request. */
    public const MAX_URLS = 10000;

    /**
     * Unique URLs on the given host, in chunks of at most $max.
     * IndexNow rejects the whole request if one URL is on another host.
     *
     * @param string[] $urls
     * @return string[][]
     */
    public static function chunks(string $host, array $urls, int $max = self::MAX_URLS): array
    {
        // No host (relative or unresolved base URL): IndexNow would reject the request
        if ($host === '') {
            return [];
        }

        $own = array_filter($urls, fn($url) => strcasecmp((string)parse_url($url, PHP_URL_HOST), $host) === 0);

        return array_chunk(array_values(array_unique($own)), $max);
    }

    /** @param string[] $urls */
    public static function body(string $host, string $key, string $keyLocation, array $urls): array
    {
        return ['host' => $host, 'key' => $key, 'keyLocation' => $keyLocation, 'urlList' => array_values($urls)];
    }
}
