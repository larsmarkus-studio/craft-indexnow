<?php

declare(strict_types=1);

namespace larsmarkusstudio\indexnow\controllers;

use craft\web\Controller;
use larsmarkusstudio\indexnow\Plugin;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Serves the verification file `/{key}.txt`, so nothing has to be committed to `web/`.
 */
class KeyController extends Controller
{
    protected array|bool|int $allowAnonymous = true;

    public function actionIndex(string $key): Response
    {
        $settings = Plugin::getInstance()->getSettings();

        if (!$settings->isEnabled() || !hash_equals($settings->getKey(), $key)) {
            throw new NotFoundHttpException();
        }

        $this->response->getHeaders()->set('Content-Type', 'text/plain; charset=utf-8');

        return $this->asRaw($key);
    }
}
