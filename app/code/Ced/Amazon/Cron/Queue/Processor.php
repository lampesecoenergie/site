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

use Ced\Amazon\Api\Processor\ProductProcessorInterface;

/**
 * Class Processor
 * @package Ced\Amazon\Cron\Queue
 */
class Processor extends \Ced\Amazon\Cron\Queue\Product\Base implements ProductProcessorInterface
{
    /**
     * Execute Process
     * @return bool
     */
    public function execute()
    {
        try {
            // Getting cron status from cache for setting type of feed.
            $status = $this->cache->getValue($this->cacheIdentifier);
            if (isset($status) && !empty($status)) {
                $this->status = $status;
            }

            // Get the status of the next staged action.
            $override = $this->getTypeOverride();
            if (isset($override)) {
                $this->type = $override;
                $this->current = $override;
            } else {
                $this->type = array_search(true, $this->status);
                $this->current = $this->type;
            }
            // Setting the default action to Product upload if none are staged for operation.
            if ($this->type == false) {
                $this->type = \Amazon\Sdk\Api\Feed::PRODUCT_INVENTORY;
            }

            // Setting the default operation type to ['Update', 'PartialUpdate'],
            // others are 'Delete' and 'PartialUpdate'.
            $operationType = [
                \Amazon\Sdk\Base::OPERATION_TYPE_UPDATE,
                \Amazon\Sdk\Base::OPERATION_TYPE_PARTIAL_UPDATE
            ];

            // Setting operation type to 'Delete' in case of delete feed.
            if ($this->type == self::PRODUCT_DELETE) {
                $operationType = \Amazon\Sdk\Base::OPERATION_TYPE_DELETE;
            }

            $this->logger->info(
                "Queue processing: execution started.",
                ['type' => $this->type, 'processor' => self::PROCESSOR_TYPE, 'operationType' => $operationType]
            );

            /** @var \Ced\Amazon\Api\Data\QueueInterface $item */
            $item = $this->queue->pop($this->type, \Ced\Amazon\Model\Source\Queue\Status::SUBMITTED, $operationType);

            /** @var \Ced\Amazon\Api\Data\QueueSearchResultsInterface $list */
            $list = $this->getList($item);

            // If current set operation have queue records, then process them.
            if ($list->getTotalCount() > 0) {
                /** @var \Ced\Amazon\Api\Data\QueueInterface[] $items */
                $items = $list->getItems();
                $this->process($items);
            } else {
                $this->status[$this->type] = false;

                $this->logger->info(
                    self::LOGGING_TAG . "current status set to false.",
                    ['type' => $this->type, 'status' => $this->status, 'processor' => self::PROCESSOR_TYPE]
                );

                foreach ($this->status as $type => $value) {
                    $this->type = $type;

                    $operationType = \Amazon\Sdk\Base::OPERATION_TYPE_UPDATE;

                    // Setting operation type to 'Delete' in case of delete feed
                    if ($this->type == self::PRODUCT_DELETE) {
                        $type = \Amazon\Sdk\Api\Feed::PRODUCT;
                        $operationType = \Amazon\Sdk\Base::OPERATION_TYPE_DELETE;
                    }

                    /** @var \Ced\Amazon\Api\Data\QueueInterface $item */
                    $item = $this->queue->pop(
                        $type,
                        \Ced\Amazon\Model\Source\Queue\Status::SUBMITTED,
                        $operationType
                    );

                    // Getting the queue records for current feed type
                    $list = $this->getList($item);

                    if ($list->getTotalCount() > 0) {
                        /** @var \Ced\Amazon\Api\Data\QueueInterface[] $items */
                        $items = $list->getItems();
                        $this->process($items);
                        break;
                    } else {
                        $this->stageNext();
                        continue;
                    }
                }
            }
            return true;
        } catch (\Exception $exception) {
            $this->logger->error(
                self::LOGGING_TAG . "failed.",
                [
                    'path' => __METHOD__,
                    'message' => $exception->getMessage(),
                    'exception' => $exception->getTraceAsString(),
                    'processor' => self::PROCESSOR_TYPE
                ]
            );
            return false;
        }
    }

    /**
     * Get List
     * @param \Ced\Amazon\Api\Data\QueueInterface $item
     * @return \Ced\Amazon\Api\Data\QueueSearchResultsInterface
     */
    private function getList($item)
    {
        /** @var \Magento\Framework\Api\Filter $statusFilter */
        $statusFilter = $this->filter->create();
        $statusFilter->setField(\Ced\Amazon\Model\Queue::COLUMN_STATUS)
            ->setConditionType('eq')
            ->setValue($item->getStatus());

        /** @var \Magento\Framework\Api\Filter $typeFilter */
        $typeFilter = $this->filter->create();
        $typeFilter->setField(\Ced\Amazon\Model\Queue::COLUMN_TYPE)
            ->setConditionType('eq')
            ->setValue($item->getType());

        /** @var \Magento\Framework\Api\Filter $operationTypeFilter */
        $operationTypeFilter = $this->filter->create();
        $operationTypeFilter->setField(\Ced\Amazon\Model\Queue::COLUMN_OPERATION_TYPE)
            ->setConditionType('eq')
            ->setValue($item->getOperationType());

        /** @var \Magento\Framework\Api\Filter $marketplaceFilter */
        $marketplaceFilter = $this->filter->create();
        $marketplaceFilter->setField(\Ced\Amazon\Model\Queue::COLUMN_MARKETPLACE)
            ->setConditionType('eq')
            ->setValue($item->getMarketplace());

        /** @var \Magento\Framework\Api\Filter $accountIdFilter */
        $accountIdFilter = $this->filter->create();
        $accountIdFilter->setField(\Ced\Amazon\Model\Queue::COLUMN_ACCOUNT_ID)
            ->setConditionType('eq')
            ->setValue($item->getAccountId());

        /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder $criteria */
        $criteria = $this->search->create();
        $criteria->addFilter($statusFilter);
        $criteria->addFilter($typeFilter);
        $criteria->addFilter($operationTypeFilter);
        $criteria->addFilter($marketplaceFilter);
        $criteria->addFilter($accountIdFilter);

        // Getting the queue records for current feed type.
        /** @var \Ced\Amazon\Api\Data\QueueSearchResultsInterface $list */
        $list = $this->queue->getList($criteria->create());

        return $list;
    }

    /**
     * Process queue items
     * @param \Ced\Amazon\Api\Data\QueueInterface[] $items
     * @throws \Exception
     */
    public function process(array $items)
    {
        $entityIdsList = [];
        $entityIdsTotal = 0;
        $itemIds = [];
        /** @var \Amazon\Sdk\Envelope|null $envelope */
        $envelope = null;
        $chunkSize = $this->config->getFeedSize($this->type);
        $specifics = [];
        foreach ($items as $item) {
            try {
                $specifics = $item->getSpecifics();
                if (isset($specifics['ids']) && !empty($specifics['ids'])) {
                    // $entityIds can be product id or shipment id
                    $entityIds = $specifics['ids'];
                    $entityIdsCount = count($entityIds);
                    if (isset($envelope) && ($envelope->index + $entityIdsCount) > $chunkSize) {
                        break;
                    }

                    // Adding data to envelope.
                    $envelope = $this->prepareAction($specifics, $envelope, $this->type, $item->getOperationType());
                    $itemIds[] = $item->getId();
                    $item->setStatus(\Ced\Amazon\Model\Source\Queue\Status::PROCESSED);
                    $item->setExecutedAt($this->dateTime->gmtDate());
                    $entityIdsList = array_merge($entityIdsList, $entityIds);
                    $entityIdsTotal += $entityIdsCount;
                }
            } catch (\Exception $e) {
                $item->setStatus(\Ced\Amazon\Model\Source\Queue\Status::ERROR);
                $this->logger->error(
                    "Queue item processing failure. Exception: " . $e->getMessage(),
                    [
                        'path' => __METHOD__,
                        'type' => $this->type,
                        'item' => $item->getData(),
                        'processor' => self::PROCESSOR_TYPE
                    ]
                );
            }

            $this->queue->save($item);
        }

        $specifics['ids'] = $entityIdsList;
        $response = $this->feed->send($envelope, $specifics);
        $feedId = isset($response['FeedSubmissionId']) ? $response['FeedSubmissionId'] : "0";
        $this->queue->addFeedId($itemIds, $feedId);
        $this->logger->info(
            self::LOGGING_TAG . "feed processed.",
            [
                'path' => __METHOD__,
                'response' => $response,
                'type' => $this->type,
                'queue_ids' => $itemIds,
                'processor' => self::PROCESSOR_TYPE
            ]
        );

        $this->stageNext();

        // Adding Results for CLI
        $this->setResult([
            "items_processed" => $entityIdsTotal,
            "current" => $this->current,
            "type" => $this->type,
            "allowed_feed_size" => $chunkSize,
        ]);
    }

    /**
     * Performing the set action by preparing the feed data
     * @param array $specifics
     * @param \Amazon\Sdk\Envelope|null $envelope
     * @param string $type
     * @param string $operationType
     * @return \Amazon\Sdk\Envelope|null
     * @throws \Exception
     */
    public function prepareAction(
        $specifics = [],
        $envelope = null,
        $type = \Amazon\Sdk\Api\Feed::PRODUCT,
        $operationType = \Amazon\Sdk\Base::OPERATION_TYPE_UPDATE
    ) {
        switch ($type) {
            case \Amazon\Sdk\Api\Feed::PRODUCT:
                $envelope = $this->product->prepare($specifics, $envelope, $operationType);
                break;
            case \Amazon\Sdk\Api\Feed::PRODUCT_PRICING:
                $envelope = $this->price->prepare($specifics, $envelope);
                break;
            case \Amazon\Sdk\Api\Feed::PRODUCT_INVENTORY:
                $envelope = $this->inventory->prepare($specifics, $envelope);
                break;
            case \Amazon\Sdk\Api\Feed::PRODUCT_IMAGE:
                $envelope = $this->image->prepare($specifics, $envelope);
                break;
            case \Amazon\Sdk\Api\Feed::PRODUCT_RELATIONSHIP:
                $envelope = $this->relation->prepare($specifics, $envelope);
                break;
            case \Amazon\Sdk\Api\Feed::ORDER_FULFILLMENT:
                $envelope = $this->shipment->prepare($specifics, $envelope);
                break;
            case self::PRODUCT_DELETE:
                $envelope = $this->product->prepareDelete($specifics, $envelope);
                break;
            default:
                $types = implode('|', array_keys($this->init));
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Invalid 'type' for the action. Current value: {$type}. Allowed values: {$types}")
                );
        }

        return $envelope;
    }
}
