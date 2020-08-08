<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Feed\Cron;

use Magento\Framework\App\State as AppState;
use Magento\Framework\Stdlib\DateTime\TimezoneInterfaceFactory;
use Mirasvit\Feed\Model\Feed;
use Mirasvit\Feed\Model\Feed\DelivererFactory;
use Mirasvit\Feed\Model\Feed\ExporterFactory;
use Mirasvit\Feed\Model\Feed\History;
use Mirasvit\Feed\Model\ResourceModel\Feed\Collection as FeedCollection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Export
{
    /**
     * @var AppState
     */
    protected $appState;

    /**
     * @var TimezoneInterfaceFactory
     */
    protected $timezoneFactory;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var FeedCollection
     */
    protected $feedCollection;

    /**
     * @var ExporterFactory
     */
    protected $exporterFactory;

    /**
     * @var DelivererFactory
     */
    protected $delivererFactory;

    /**
     * @var History
     */
    protected $history;

    public function __construct(
        AppState $appState,
        TimezoneInterfaceFactory $timezoneFactory,
        FeedCollection $feedCollection,
        ExporterFactory $exporterFactory,
        DelivererFactory $delivererFactory,
        History $history
    ) {
        $this->appState = $appState;
        $this->timezoneFactory = $timezoneFactory;
        $this->feedCollection = $feedCollection;
        $this->exporterFactory = $exporterFactory;
        $this->delivererFactory = $delivererFactory;
        $this->history = $history;
    }

    /**
     * Export and delivery feeds
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function execute()
    {
        $collection = $this->feedCollection
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('cron', 1);

        /** @var Feed $feed */
        foreach ($collection as $feed) {
            if ($this->canExport($feed) >= 0) {
                $feed = $feed->load($feed->getId());
                $exporter = $this->exporterFactory->create();
                $deliverer = $this->delivererFactory->create();

                try {
                    foreach ($exporter->exportCli($feed) as $status => $message) {

                    }

                    if ($feed->getFtp()) {
                        $deliverer->delivery($feed);
                    }
                } catch (\Exception $e) {
                    $this->history->add($feed, 'Exception', $e->getMessage());
                }
            } else {
                $this->history->add($feed, 'Cron', 'Skip cron job.');
            }
        }
    }

    /**
     * Check conditions for ability to run feed export by cron
     *
     * @param Feed $feed
     * @return int
     */
    public function canExport(Feed $feed)
    {
        $result = -1;

        $this->timezone = $this->timezoneFactory->create();

        $currentDay       = (int)$this->timezone->date()->format('w');
        $currentDayOfYear = (int)$this->timezone->date()->format('z');
        $currentTime      = (int)$this->timezone->date()->format('G') * 60 + (int)$this->timezone->date()->format('i');
        $currentYear      = (int)$this->timezone->date()->format('Y');

        $lastRun       = strtotime($feed->getGeneratedAt());
        $lastDayOfYear = (int)$this->timezone->date($lastRun)->format('z');
        $lastTime      = (int)$this->timezone->date($lastRun)->format('G') * 60 + (int)$this->timezone->date($lastRun)->format('i');
        $lastRunYear   = (int)$this->timezone->date($lastRun)->format('Y');

        if (!$feed->getGeneratedAt()) {
            $lastTime = $currentTime - 25;
        }

        // we run generation minimum day ago. Need run generation
        if ($currentDayOfYear > $lastDayOfYear || $currentYear > $lastRunYear) {
            $lastTime = 0;
        }

        if (in_array($currentDay, $feed->getCronDay())) {
            foreach ($feed->getCronTime() as $cronTime) {
                if ($currentTime >= $cronTime
                    && $cronTime >= $lastTime
                    && $currentTime - $lastTime > 10
                ) {
                    $result = $cronTime;
                    break;
                }
            }
        }

        return $result;
    }
}
