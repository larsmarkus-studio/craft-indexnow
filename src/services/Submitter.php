<?php

declare(strict_types=1);

namespace larsmarkusstudio\indexnow\services;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\helpers\ElementHelper;
use craft\helpers\Queue;
use craft\helpers\UrlHelper;
use larsmarkusstudio\indexnow\helpers\Payload;
use larsmarkusstudio\indexnow\jobs\SubmitUrlsJob;
use larsmarkusstudio\indexnow\Plugin;

/**
 * Collects entry URLs per site and queues them; the job does the HTTP call.
 */
class Submitter extends Component
{
    /**
     * Published saves only: no drafts (including autosaves), revisions, propagated copies,
     * resaves or nested (Matrix) entries, and only sections that are configured.
     */
    public function shouldSubmit(mixed $element): bool
    {
        $settings = Plugin::getInstance()->getSettings();

        return $element instanceof Entry
            && $settings->isEnabled()
            && $element->getSection() !== null
            && !ElementHelper::isDraftOrRevision($element)
            && !$element->propagating
            && !$element->resaving
            && (!$settings->sections || in_array($element->getSection()->handle, $settings->sections, true));
    }

    /**
     * URLs of all sites the entry is live in. Before a delete that is still the public set,
     * so never-published URLs aren't leaked.
     */
    public function queueEntry(Entry $entry): void
    {
        $entries = Entry::find()->id($entry->id)->site('*')->unique(false)->uri(':notempty:')
            ->status(Entry::STATUS_LIVE)->all();

        $this->queueUrls($entries);
    }

    /** @param Entry[] $entries */
    public function queueUrls(iterable $entries): int
    {
        $bySite = [];
        foreach ($entries as $entry) {
            $bySite[$entry->siteId][] = $entry->getUrl();
        }

        $queued = 0;
        foreach ($bySite as $siteId => $urls) {
            $host = (string)parse_url(UrlHelper::siteUrl('', null, null, $siteId), PHP_URL_HOST);
            foreach (Payload::chunks($host, array_filter($urls)) as $chunk) {
                Queue::push(new SubmitUrlsJob(['siteId' => $siteId, 'urls' => $chunk]));
                $queued += count($chunk);
            }
        }

        return $queued;
    }

    /** Throws on anything but 200/202, so the queue retries. */
    public function submit(int $siteId, array $urls): void
    {
        $settings = Plugin::getInstance()->getSettings();
        $key = $settings->getKey();
        $baseUrl = UrlHelper::siteUrl('', null, null, $siteId);
        $body = Payload::body((string)parse_url($baseUrl, PHP_URL_HOST), $key, UrlHelper::siteUrl("$key.txt", null, null, $siteId), $urls);

        Craft::createGuzzleClient(['timeout' => 20])->post($settings->endpoint, ['json' => $body]);
    }
}
