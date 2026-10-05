<?php

declare(strict_types=1);

namespace larsmarkusstudio\indexnow;

use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\events\ElementEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Elements;
use craft\web\UrlManager;
use larsmarkusstudio\indexnow\models\Settings;
use larsmarkusstudio\indexnow\services\Submitter;
use yii\base\Event;

/**
 * IndexNow plugin: tells search engines about new, changed and deleted entries.
 *
 * @author Lars Markus
 * @license MIT
 *
 * @property-read Settings $settings
 * @property-read Submitter $submitter
 * @method Settings getSettings()
 */
class Plugin extends BasePlugin
{
    public string $schemaVersion = '1.0.0';

    public static function config(): array
    {
        return ['components' => ['submitter' => Submitter::class]];
    }

    public function init(): void
    {
        parent::init();

        // Only the literal key, so other `*.txt` templates and routes keep working
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES, function (RegisterUrlRulesEvent $event) {
            if ($this->getSettings()->isEnabled()) {
                $event->rules[$this->getSettings()->getKey() . '.txt'] = 'lms-indexnow/key/index';
            }
        });

        // Fires after the save transaction commits
        Event::on(Elements::class, Elements::EVENT_AFTER_SAVE_ELEMENT, function (ElementEvent $event) {
            if ($this->submitter->shouldSubmit($event->element)) {
                $this->submitter->queueEntry($event->element);
            }
        });

        // Before: afterwards the other sites' URLs can no longer be looked up
        Event::on(Elements::class, Elements::EVENT_BEFORE_DELETE_ELEMENT, function (ElementEvent $event) {
            if ($this->submitter->shouldSubmit($event->element)) {
                $this->submitter->queueEntry($event->element);
            }
        });
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }
}
