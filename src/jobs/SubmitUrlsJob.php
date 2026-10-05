<?php

declare(strict_types=1);

namespace larsmarkusstudio\indexnow\jobs;

use Craft;
use craft\queue\BaseJob;
use larsmarkusstudio\indexnow\Plugin;
use Throwable;
use yii\queue\RetryableJobInterface;

/**
 * Submits URLs of one site to IndexNow. The URLs are fixed at queue time, so a deleted
 * entry's URL is still sent.
 */
class SubmitUrlsJob extends BaseJob implements RetryableJobInterface
{
    public int $siteId;

    /** @var string[] */
    public array $urls = [];

    public function execute($queue): void
    {
        try {
            Plugin::getInstance()->submitter->submit($this->siteId, $this->urls);
        } catch (Throwable $e) {
            Craft::error("Site $this->siteId, " . count($this->urls) . " URLs: {$e->getMessage()}", 'lms-indexnow');
            throw $e;
        }
    }

    public function getTtr(): int
    {
        return 60;
    }

    public function canRetry($attempt, $error): bool
    {
        return $attempt < 3;
    }

    protected function defaultDescription(): ?string
    {
        return 'Submitting ' . count($this->urls) . ' URLs to IndexNow';
    }
}
