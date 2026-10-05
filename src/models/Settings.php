<?php

declare(strict_types=1);

namespace larsmarkusstudio\indexnow\models;

use Craft;
use craft\base\Model;
use craft\helpers\App;

/**
 * Plugin settings. Override in `config/lms-indexnow.php` (multi-environment arrays work).
 * `key` may be an env var reference (`$INDEXNOW_KEY`); for the others use `App::env()` in the config file.
 */
class Settings extends Model
{
    /** 8–128 characters: letters, digits and dashes. Served at `/{key}.txt`. */
    public string $key = '$INDEXNOW_KEY';

    /** Default: on, except in devMode, so local saves don't ping search engines. */
    public ?bool $enabled = null;

    /** Any IndexNow endpoint shares submissions with the others. */
    public string $endpoint = 'https://api.indexnow.org/indexnow';

    /** Section handles to submit. Empty: all sections. */
    public array $sections = [];

    public function getKey(): string
    {
        return trim((string)App::parseEnv($this->key));
    }

    public function isEnabled(): bool
    {
        $enabled = $this->enabled ?? !Craft::$app->getConfig()->getGeneral()->devMode;

        return $enabled && preg_match('/^[A-Za-z0-9-]{8,128}$/', $this->getKey()) === 1;
    }
}
