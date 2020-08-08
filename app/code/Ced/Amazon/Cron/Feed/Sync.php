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

namespace Ced\Amazon\Cron\Feed;

/**
 * @deprecated
 * Class Sync
 * @package Ced\Amazon\Cron\Feed
 */
class Sync
{
    /**
     * @var \Ced\Amazon\Helper\Logger
     */
    public $logger;

    /**
     * @var \Ced\Amazon\Api\FeedRepositoryInterface
     */
    public $feed;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder
     */
    public $search;

    /**
     * @var \Magento\Framework\Api\FilterFactory
     */
    public $filter;

    /** @var \Magento\Catalog\Model\ProductFactory  */
    public $product;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    public $productResource;

    public function __construct(
        \Magento\Framework\Api\FilterFactory $filter,
        \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory $search,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Ced\Amazon\Api\FeedRepositoryInterface $feed,
        \Ced\Amazon\Helper\Logger $logger
    ) {
        $this->filter = $filter;
        $this->search = $search;
        $this->product = $productFactory;
        $this->productResource = $productResource;
        $this->feed = $feed;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            $feeds = $this->getList();
            if ($feeds->getTotalCount() > 0) {
                /** @var \Ced\Amazon\Model\Feed $item */
                foreach ($feeds->getItems() as $item) {
                    $status = $this->feed->sync($item->getId(), $item);
                    $this->update($status, $item);
                }
            }

            $feeds = $this->getList(
                \Ced\Amazon\Model\Source\Feed\Status::DONE,
                [
                    \Amazon\Sdk\Api\Feed::PRODUCT,
                    \Amazon\Sdk\Api\Feed::PRODUCT_PRICING,
                    \Amazon\Sdk\Api\Feed::PRODUCT_INVENTORY,
                    \Amazon\Sdk\Api\Feed::PRODUCT_RELATIONSHIP,
                ]
            );
            if ($feeds->getTotalCount() > 0) {
                /** @var \Ced\Amazon\Model\Feed $item */
                foreach ($feeds->getItems() as $item) {
                    $this->update(\Ced\Amazon\Model\Source\Feed\Status::DONE, $item);
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), ['path' => __METHOD__]);
        }
    }

    /**
     * Process feed result and update products. TODO: add order and shipment status update.
     * @param string $status
     * @param \Ced\Amazon\Api\Data\FeedInterface $item
     * @throws \Exception
     */
    private function update($status, $item)
    {
        if ($status == \Ced\Amazon\Model\Source\Feed\Status::DONE &&
            in_array(
                $item->getType(),
                [
                    \Amazon\Sdk\Api\Feed::PRODUCT,
                    \Amazon\Sdk\Api\Feed::PRODUCT_PRICING,
                    \Amazon\Sdk\Api\Feed::PRODUCT_IMAGE,
                    \Amazon\Sdk\Api\Feed::PRODUCT_INVENTORY,
                    \Amazon\Sdk\Api\Feed::PRODUCT_RELATIONSHIP,
                ]
            )
        ) {
            /** @var array $response */
            $response = $item->getResponse();
            /** @var array $specifics */
            $specifics = json_decode($item->getSpecifics(), true);
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
                            $data[$message['AdditionalInfo']['SKU']]['AccountId'] = $specifics['account_id'];
                        }
                        if (isset($specifics['marketplace'])) {
                            $data[$message['AdditionalInfo']['SKU']]['Marketplace'] = $specifics['marketplace'];
                        }
                        $data[$message['AdditionalInfo']['SKU']]['FeedId'] = $item->getFeedId();
                        $data[$message['AdditionalInfo']['SKU']]['Result'][] = $value;
                    }
                }

                if (!empty($data)) {
                    $specifics = json_decode($item->getSpecifics(), true);
                    if (is_array($specifics) && isset($specifics['store_id'])) {
                        /** @var  \Magento\Catalog\Model\Product $product */
                        $product = $this->product->create();
                        foreach ($data as $sku => $values) {
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
        }

        $item->setStatus(\Ced\Amazon\Model\Source\Feed\Status::SYNCED);
        $this->feed->save($item);
    }

    /**
     * Get List
     * @return \Ced\Amazon\Api\Data\FeedSearchResultsInterface
     * @param string $status
     * @param null|array $type
     * @throws \Exception
     */
    private function getList($status = \Ced\Amazon\Model\Source\Feed\Status::SUBMITTED, $type = null)
    {
        /** @var \Magento\Framework\Api\Filter $statusFilter */
        $statusFilter = $this->filter->create();
        $statusFilter->setField(\Ced\Amazon\Model\Feed::COLUMN_STATUS)
            ->setConditionType('eq')
            ->setValue($status);

        if (!empty($type)) {
            /** @var \Magento\Framework\Api\Filter $typeFilter */
            $typeFilter = $this->filter->create();
            $typeFilter->setField(\Ced\Amazon\Model\Feed::COLUMN_TYPE)
                ->setConditionType('in')
                ->setValue($type);
        }

        /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder $criteria */
        $criteria = $this->search->create();
        $criteria->addFilter($statusFilter);
        if (!empty($typeFilter)) {
            $criteria->addFilter($typeFilter);
        }

        $criteria->setPageSize(10);
        $criteria->setCurrentPage(1);
        $criteria->create();

        // Getting the feed records for current feed type.
        /** @var \Ced\Amazon\Api\Data\FeedSearchResultsInterface $list */
        $list = $this->feed->getList($criteria->create());

        return $list;
    }
}
