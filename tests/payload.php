<?php

// Run: php -d zend.assertions=1 tests/payload.php (no Craft needed). Exits non-zero on failure.

declare(strict_types=1);

require __DIR__ . '/../src/helpers/Payload.php';

use larsmarkusstudio\indexnow\helpers\Payload;

if (ini_get('zend.assertions') !== '1') {
    fwrite(STDERR, "Run with assertions on: php -d zend.assertions=1 tests/payload.php\n");
    exit(1);
}
ini_set('assert.exception', '1');

$urls = [
    'https://example.com/a',
    'https://EXAMPLE.com/b',   // host is case-insensitive
    'https://example.com/a',   // duplicate
    'https://other.com/c',     // other host: IndexNow would reject the whole request
    'not a url',
];

$chunks = Payload::chunks('example.com', $urls);
assert($chunks === [['https://example.com/a', 'https://EXAMPLE.com/b']], json_encode($chunks));

$chunks = Payload::chunks('example.com', ['https://example.com/1', 'https://example.com/2', 'https://example.com/3'], 2);
assert(count($chunks) === 2 && count($chunks[1]) === 1);

assert(Payload::chunks('example.com', []) === []);

// No site host: relative URLs (host '') must not slip through
assert(Payload::chunks('', ['/a', 'not a url']) === []);

$body = Payload::body('example.com', 'abcd1234', 'https://example.com/abcd1234.txt', [3 => 'https://example.com/a']);
assert($body['urlList'] === ['https://example.com/a'] && $body['host'] === 'example.com');

echo "payload: ok\n";
