<?php

declare(strict_types=1);

namespace larsmarkusstudio\indexnow\console\controllers;

use craft\console\Controller;
use craft\elements\Entry;
use larsmarkusstudio\indexnow\Plugin;
use yii\console\ExitCode;

/**
 * Queues every live entry URL, e.g. after the first deploy:
 *
 *     craft lms-indexnow/submit/all
 */
class SubmitController extends Controller
{
    public function actionAll(): int
    {
        $plugin = Plugin::getInstance();
        $settings = $plugin->getSettings();

        if (!$settings->isEnabled()) {
            $this->stderr("Disabled: set a valid INDEXNOW_KEY and turn 'enabled' on (it is off in devMode by default).\n");
            return ExitCode::CONFIG;
        }

        $entries = Entry::find()->section($settings->sections ?: null)->site('*')->unique(false)
            ->status(Entry::STATUS_LIVE)->uri(':notempty:')->each();

        $this->stdout('Queued ' . $plugin->submitter->queueUrls($entries) . " URLs.\n");

        return ExitCode::OK;
    }
}
