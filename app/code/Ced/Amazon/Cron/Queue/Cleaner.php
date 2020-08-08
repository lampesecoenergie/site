<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Cron\Queue;

/**
 * Class Cleaner
 * @package Ced\Amazon\Cron\Queue
 */
class Cleaner
{
    /**
     * @var \Ced\Amazon\Helper\Logger
     */
    public $logger;

    /** @var \Ced\Amazon\Model\ResourceModel\Queue\CollectionFactory  */
    public $queueCollectionFactory;

    /** @var \Ced\Amazon\Repository\Report  */
    public $reportRepository;

    /** @var \Ced\Amazon\Repository\Feed  */
    public $feedRepository;

    /** @var \Magento\Framework\Stdlib\DateTime\DateTime  */
    public $dateTime;

    /** @var bool  */
    public $all = false;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Ced\Amazon\Model\ResourceModel\Queue\CollectionFactory $queueCollectionFactory,
        \Ced\Amazon\Repository\Report $reportRepository,
        \Ced\Amazon\Repository\Feed $feedRepository,
        \Ced\Amazon\Helper\Logger $logger
    ) {
        $this->dateTime = $dateTime;
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->reportRepository = $reportRepository;
        $this->feedRepository = $feedRepository;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            $this->logger->debug('Queue clear cron run.');

            // Clearing the "Done" queue jobs
            /** @var \Ced\Amazon\Model\ResourceModel\Queue\Collection $queueRecords */
            $queueRecords = $this->queueCollectionFactory->create();
            if (!$this->getClearAll()) {
                $queueRecords->addFieldToFilter('status', ['eq' => \Ced\Amazon\Model\Source\Queue\Status::DONE]);
            }

            $size = $queueRecords->getSize();
            if (isset($queueRecords) && $size > 0) {
                $queueRecords->walk('delete');
                $this->logger->debug("Queue {$size} record(s) deleted for 'Done' status.");
            }

            // Clearing 6 days old queue jobs
            $created = $this->dateTime->gmtDate("Y-m-d", strtotime('-6 days'));
            $queueRecords = $this->queueCollectionFactory->create();
            $queueRecords->addFieldToFilter(\Ced\Amazon\Model\Queue::COLUMN_CREATED_AT, ['lteq' => $created]);

            $size = $queueRecords->getSize();
            if (isset($queueRecords) && $size > 0) {
                $queueRecords->walk('delete');
                $this->logger->debug("Queue {$size} record(s) deleted for 6 days old.", ["created" => $created]);
            }

            // Clearing 6 days old feeds records
            $status = $this->feedRepository->clearRecords($created);
            if ($status) {
                $status = (string)$status;
                $this->logger->debug("Feed {$status} record(s) deleted.", ["created" => $created]);
            }

            // Clearing 6 days old report records
            $status = $this->reportRepository->clearRecords($created);
            if ($status) {
                $status = (string)$status;
                $this->logger->debug("Report {$status} record(s) deleted.", ["created" => $created]);
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), ["method" => __METHOD__]);
        }
    }

    /**
     * Set Clear All
     * @param boolean $flag
     */
    public function setClearAll($flag)
    {
        $this->all = (boolean)$flag;
    }

    /**
     * Get Clear All
     * @return bool
     */
    public function getClearAll()
    {
        return $this->all;
    }
}
