<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Cron\Queue;

/**
 * Class Sychronize
 * @package Ced\Amazon\Cron\Queue
 */
class Sychronize
{
    const LIMIT = 1;

    /**
     * Json Parser
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    public $serializer;

    /**
     * @var \Amazon\Sdk\EnvelopeFactory
     */
    public $envelope;

    /**
     * @var \Ced\Amazon\Api\QueueRepositoryInterface
     */
    public $queue;

    /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory $search */
    public $search;

    /** @var \Magento\Framework\Api\FilterFactory */
    public $filter;

    /** @var \Magento\Catalog\Model\ProductFactory */
    public $product;

    public $type = null;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    public $productResource;

    /** @var \Ced\Amazon\Helper\Shipment */
    public $shipment;

    /** @var \Ced\Amazon\Helper\Logger */
    public $logger;

    /** @var \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory */
    public $shipmentCollectionFactory;

    /** @var string */
    public $result;

    public function __construct(
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory $search,
        \Magento\Framework\Api\FilterFactory $filter,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory,
        \Ced\Amazon\Api\QueueRepositoryInterface $queue,
        \Ced\Amazon\Api\FeedRepositoryInterface $feed,
        \Ced\Amazon\Helper\Shipment $shipment,
        \Ced\Amazon\Helper\Logger $logger
    )
    {
        $this->serializer = $serializer;
        $this->search = $search;
        $this->filter = $filter;
        $this->product = $productFactory;
        $this->productResource = $productResource;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;

        $this->queue = $queue;
        $this->feed = $feed;
        $this->shipment = $shipment;
        $this->logger = $logger;
    }

    public function execute()
    {
        $items = $this->getList();
        $result = [];
        if ($items->getTotalCount() > 0) {
            /** @var \Ced\Amazon\Api\Data\QueueInterface $item */
            foreach ($items->getItems() as $item) {
                $id = $item->getId();
                $result[] = "queue_id: {$id}";
                /** @var array $specifics */
                $specifics = $item->getSpecifics();
                if (isset($specifics['feed_id'])) {
                    if ($specifics['feed_id'] != '0') {
                        $this->update($item, $specifics);
                    } else {
                        $this->queue->save($item->setStatus(\Ced\Amazon\Model\Source\Queue\Status::ERROR));
                    }
                }
            }
        }

        $this->setResult(json_encode($result));
    }

    /**
     * @param \Ced\Amazon\Api\Data\QueueInterface $item
     * @param array $specifics
     */
    private function update($item, array $specifics = [])
    {
        try {
            /** @var \Ced\Amazon\Api\Data\FeedInterface $feed */
            $feed = $this->feed->getByFeedId($specifics['feed_id']);
            $status = $feed->getStatus();

            if (isset($status) && $status != \Ced\Amazon\Model\Source\Queue\Status::DONE) {
                // Sync feed and get status
                $this->feed->sync($feed->getId(), $feed);
                $status = $feed->getStatus();

                // If feed Status is Done, then update the queue status as well as update the error message
                if ($status == \Ced\Amazon\Model\Source\Queue\Status::DONE) {
                    // Update message on product
                    if (in_array(
                        $feed->getType(),
                        [
                            \Amazon\Sdk\Api\Feed::PRODUCT,
                            \Amazon\Sdk\Api\Feed::PRODUCT_PRICING,
                            \Amazon\Sdk\Api\Feed::PRODUCT_IMAGE,
                            \Amazon\Sdk\Api\Feed::PRODUCT_INVENTORY,
                            \Amazon\Sdk\Api\Feed::PRODUCT_RELATIONSHIP,
                        ]
                    )) {
                        /** @var array $response */
                        $response = $feed->getResponse();
                        /** @var array $specifics */
                        $specifics = json_decode($feed->getSpecifics(), true);
                        if (isset($response['AmazonEnvelope']['_value']['Message']['ProcessingReport']['Result']) &&
                            is_array($response['AmazonEnvelope']['_value']['Message']['ProcessingReport']['Result'])
                        ) {
                            $results = $response['AmazonEnvelope']['_value']['Message']['ProcessingReport']['Result'];
                            $data = [];
                            foreach ($results as $message) {
                                if (isset($message['AdditionalInfo']['SKU'])) {
                                    $value = $message;
                                    unset($value['MessageID']);
                                    unset($value['AdditionalInfo']);
                                    if (isset($specifics['account_id'])) {
                                        $data[$message['AdditionalInfo']['SKU']]['AccountId'] =
                                            $specifics['account_id'];
                                    }

                                    if (isset($specifics['marketplace'])) {
                                        $data[$message['AdditionalInfo']['SKU']]['Marketplace'] =
                                            $specifics['marketplace'];
                                    }

                                    $type = '';
                                    if (isset($specifics['type'])) {
                                        $type = $specifics['type'];
                                    }

                                    $data[$message['AdditionalInfo']['SKU']]['FeedId'] = $feed->getFeedId();
                                    $data[$message['AdditionalInfo']['SKU']]['Result'][] = $value;
                                    $data[$message['AdditionalInfo']['SKU']]['Type'] = $type;
                                }
                            }

                            if (!empty($data)) {
                                $specifics = json_decode($feed->getSpecifics(), true);
                                if (is_array($specifics) && isset($specifics['store_id'])) {
                                    /** @var  \Magento\Catalog\Model\Product $product */
                                    $product = $this->product->create();
                                    foreach ($data as $sku => $values) {
                                        if (is_array($values) && isset($values['Result'])
                                            && is_array($values['Result'])) {
                                            $values['Result'] = array_unique($values['Result']);
                                        }

                                        $p = $product->setStoreId($specifics['store_id'])
                                            ->loadByAttribute('sku', $sku);
                                        if ($p) {
                                            $p->setData(
                                                \Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_FEED_ERRORS,
                                                json_encode($values)
                                            );
                                            $this->productResource->saveAttribute(
                                                $p,
                                                \Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_FEED_ERRORS
                                            );
                                        }
                                    }
                                }
                            }
                        }
                        $item->setStatus(\Ced\Amazon\Model\Source\Feed\Status::SYNCED);
                        $this->feed->save($feed);

                        // Update the queue status, if the product is synced
                        $this->queue->save($item->setStatus(\Ced\Amazon\Model\Source\Queue\Status::DONE));
                    }
                }

                // If feed order fulfillment, then add feed id to order shipment.
                if ($feed->getType() == \Amazon\Sdk\Api\Feed::ORDER_FULFILLMENT) {
                    $specifics = json_decode((string)$feed->getSpecifics(), true);
                    if (!isset($specifics['shipment_sync']) &&
                        isset($specifics['ids']) &&
                        !empty($specifics['ids']) && is_array($specifics['ids'])) {
                        $collection = $this->shipmentCollectionFactory->create();
                        $collection->addFieldToFilter('entity_id', ['in' => $specifics['ids']])
                            ->addFieldToSelect(['entity_id', 'order_id'])
                            ->getSelect()
                            ->joinLeft(
                                ['amazon' => $collection->getTable('ced_amazon_order')],
                                'main_table.order_id = amazon.magento_order_id',
                                [
                                    'amazon.id as amazon_order_entity_id',
                                ]
                            );

                        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                        foreach ($collection->getItems() as $shipment) {
                            $data = [
                                'feed_id' => $feed->getId(),
                                'Status' => $feed->getStatus(),
                                'Feed' => [
                                    'FeedSubmissionId' => $feed->getFeedId(),
                                    'SubmittedDate' => $feed->getCreatedDate(),
                                ]
                            ];
                            $this->shipment->update(
                                $shipment->getData('amazon_order_entity_id'),
                                $shipment->getEntityId(),
                                $data
                            );
                        }

                        $specifics['shipment_sync'] = true;
                        $feed->setSpecifics(json_encode($specifics));
                        $this->feed->save($feed);

                        // Update the queue status, if the shipment is synced
                        $this->queue->save($item->setStatus(\Ced\Amazon\Model\Source\Queue\Status::DONE));
                    }
                }
            } else {
                // Update the queue status, if the feed is synced
                $this->queue->save($item->setStatus(\Ced\Amazon\Model\Source\Queue\Status::DONE));
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['path' => __METHOD__]);
        }
    }

    /**
     * Get List of PROCESSED queues
     * @param string $status
     * @param int $limit
     * @return \Ced\Amazon\Api\Data\QueueSearchResultsInterface
     */
    private function getList($status = \Ced\Amazon\Model\Source\Queue\Status::PROCESSED, $limit = self::LIMIT)
    {
        /** @var \Magento\Framework\Api\Filter $statusFilter */
        $statusFilter = $this->filter->create();
        $statusFilter->setField(\Ced\Amazon\Model\Queue::COLUMN_STATUS)
            ->setConditionType('eq')
            ->setValue($status);

        /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder $criteria */
        $criteria = $this->search->create();
        $criteria->addFilter($statusFilter);
        $criteria->setPageSize($limit);
        $criteria->setCurrentPage(1);

        if (!empty($this->type)) {
            /** @var \Magento\Framework\Api\Filter $typeFilter */
            $typeFilter = $this->filter->create();
            $typeFilter->setField(\Ced\Amazon\Model\Queue::COLUMN_TYPE)
                ->setConditionType('eq')
                ->setValue($this->type);
            $criteria->addFilter($typeFilter);
        }

        // Getting the queue records for current feed type.
        /** @var \Ced\Amazon\Api\Data\QueueSearchResultsInterface $list */
        $list = $this->queue->getList($criteria->create());

        return $list;
    }

    public function setType($ype)
    {
        $this->type = $ype;
    }

    public function setResult($result)
    {
        $this->result = $result;
    }

    public function getResult()
    {
        return $this->result;
    }
}
