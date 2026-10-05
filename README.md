# IndexNow for Craft CMS

Tells search engines (Bing, Yandex, Seznam, Naver and others) about new, changed and deleted
entries through [IndexNow](https://www.indexnow.org), in a queue job. Submitting to one endpoint
shares the URL with all participating engines. Google doesn't use IndexNow.

- Craft CMS 5.6+, PHP 8.2+
- A queue runner

## Installation

Until the first release is on Packagist, add the repository to your project's `composer.json`:

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/larsmarkus-studio/craft-indexnow.git" }
]
```

```bash
composer require larsmarkus-studio/craft-indexnow
php craft plugin/install lms-indexnow
```

Generate a key (8–128 characters: letters, digits, dashes) and set it in `.env`:

```bash
openssl rand -hex 16   # → INDEXNOW_KEY=<output>
```

The plugin serves `/{key}.txt` itself, so nothing goes into `web/`. Then, once, submit the existing pages:

```bash
php craft lms-indexnow/submit/all
```

## How it works

- A published entry is saved → one job per site, with that site's URL. Drafts, autosaves, revisions,
  propagated copies, resaves, entries that aren't live and entries without a URL are skipped.
- An entry is deleted → its URLs (every site) are submitted so engines drop them.
- URLs are grouped per site, with the key file at that site's base URL, in chunks of 10,000.
  A job retries twice on a failed request.
- Off in `devMode` unless `enabled` is set, so local saves don't ping anyone.

## Settings

`config/lms-indexnow.php` (multi-environment arrays work):

```php
return [
    'key' => '$INDEXNOW_KEY',                          // default
    'enabled' => null,                                 // null: on unless devMode
    'sections' => [],                                  // handles; empty: all
    'endpoint' => 'https://api.indexnow.org/indexnow', // default
];
```

## Not included

Elements other than entries, a Control Panel settings page and a submission log. Failed jobs show in
the queue manager and `storage/logs` (category `lms-indexnow`).

## Development

```bash
php -d zend.assertions=1 tests/payload.php
```
