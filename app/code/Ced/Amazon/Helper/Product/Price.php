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

namespace Ced\Amazon\Helper\Product;

class Price implements \Ced\Integrator\Helper\Product\PriceInterface
{
    /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory $search */
    public $search;

    /** @var \Magento\Framework\Api\FilterFactory */
    public $filter;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
    public $products;

    /** @var \Ced\Amazon\Api\ProfileRepositoryInterface */
    public $profile;

    /**
     * @var \Ced\Amazon\Api\QueueRepositoryInterface
     */
    public $queue;

    /** @var \Ced\Amazon\Api\Data\Queue\DataInterfaceFactory */
    public $queueDataFactory;

    /** @var \Ced\Amazon\Api\AccountRepositoryInterface */
    public $account;

    /** @var \Ced\Amazon\Api\FeedRepositoryInterface */
    public $feed;

    /** @var \Ced\Amazon\Helper\Config */
    public $config;

    /** @var \Ced\Amazon\Helper\Logger */
    public $logger;

    /** @var \Amazon\Sdk\EnvelopeFactory */
    public $envelope;

    /** @var \Amazon\Sdk\Product\PriceFactory */
    public $price;

    public function __construct(
        \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory $search,
        \Magento\Framework\Api\FilterFactory $filter,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productsFactory,
        \Ced\Amazon\Api\AccountRepositoryInterface $account,
        \Ced\Amazon\Api\ProfileRepositoryInterface $profile,
        \Ced\Amazon\Api\QueueRepositoryInterface $queue,
        \Ced\Amazon\Api\FeedRepositoryInterface $feed,
        \Ced\Amazon\Api\Data\Queue\DataInterfaceFactory $queueDataFactory,
        \Ced\Amazon\Helper\Config $config,
        \Ced\Amazon\Helper\Logger $logger,
        \Amazon\Sdk\EnvelopeFactory $envelopeFactory,
        \Amazon\Sdk\Product\PriceFactory $price
    ) {
        $this->search = $search;
        $this->filter = $filter;

        $this->products = $productsFactory;

        $this->account = $account;
        $this->profile = $profile;
        $this->feed = $feed;
        $this->queue = $queue;
        $this->queueDataFactory = $queueDataFactory;
        $this->logger = $logger;
        $this->config = $config;

        $this->envelope = $envelopeFactory;
        $this->price = $price;
    }

    /**
     * @TODO: Update price for 'uploaded' products only,
     * Update the values for provided ids
     * @param array $ids
     * @param bool $throttle
     * @param string $priority
     * @return boolean
     * @throws \Exception
     */
    public function update(
        array $ids = [],
        $throttle = true,
        $priority = \Ced\Amazon\Model\Source\Queue\Priorty::MEDIUM
    ) {
        $status = false;
        if (isset($ids) && !empty($ids)) {
            $profileIds = $this->profile->getProfileIdsByProductIds($ids);
            if (!empty($profileIds)) {
                /** @var \Magento\Framework\Api\Filter $idsFilter */
                $idsFilter = $this->filter->create();
                $idsFilter->setField(\Ced\Amazon\Model\Profile::COLUMN_ID)
                    ->setConditionType('in')
                    ->setValue($profileIds);

                /** @var \Magento\Framework\Api\Filter $statusFilter */
                $statusFilter = $this->filter->create();
                $statusFilter->setField(\Ced\Amazon\Model\Profile::COLUMN_STATUS)
                    ->setConditionType('eq')
                    ->setValue(\Ced\Amazon\Model\Source\Profile\Status::ENABLED);

                /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder $criteria */
                $criteria = $this->search->create();
                $criteria->addFilter($statusFilter);
                $criteria->addFilter($idsFilter);
                /** @var \Ced\Amazon\Api\Data\ProfileSearchResultsInterface $profiles */
                $profiles = $this->profile->getList($criteria->create());
                /** @var \Ced\Amazon\Api\Data\AccountSearchResultsInterface $accounts */
                $accounts = $profiles->getAccounts();

                /** @var array $stores */
                $stores = $profiles->getProfileByStoreIdWise();

                /** @var string $type */
                $type = $this->config->getPriceType();

                /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
                foreach ($accounts->getItems() as $accountId => $account) {
                    foreach ($stores as $storeId => $profiles) {
                        $envelope = null;
                        /** @var \Ced\Amazon\Api\Data\ProfileInterface $profile */
                        foreach ($profiles as $profileId => $profile) {
                            /** @var array $productIds */
                            $productIds = $this->profile->getAssociatedProductIds($profileId, $storeId, $ids);
                            /** @var array $marketplaceIds */
                            $marketplaceIds = $profile->getMarketplaceIds();

                            if (!empty($productIds)) {
                                if ($throttle == true) {
                                    $individual = $this->config->sendPriceFeedMPWise();
                                    // queue
                                    if ($type == \Ced\Amazon\Model\Source\Config\Price::TYPE_ATTRIBUTE || $individual) {
                                        // 1. Different price for different countries
                                        foreach ($marketplaceIds as $marketplaceId) {
                                            $status = $this->push($profile, $marketplaceId, $productIds, $priority);
                                        }
                                    } else {
                                        // 2. Same price for different countries
                                        $status = $this->push(
                                            $profile,
                                            $profile->getMarketplace(),
                                            $productIds,
                                            $priority
                                        );
                                    }
                                } else {
                                    //TODO: add all data to uniqueid in session & process via multiple ajax requests.
                                    $individual = $this->config->sendPriceFeedMPWise();
                                    // prepare & send: divide in chunks and process in multiple requests
                                    if ($type == \Ced\Amazon\Model\Source\Config\Price::TYPE_ATTRIBUTE || $individual) {
                                        // 1. Different price for different countries
                                        foreach ($marketplaceIds as $marketplaceId) {
                                            // 2. Same price for different countries
                                            $specifics = [
                                                'ids' => $productIds,
                                                'account_id' => $accountId,
                                                'marketplace' => $marketplaceId,
                                                'profile_id' => $profileId,
                                                'store_id' => $storeId,
                                                'type' => \Amazon\Sdk\Api\Feed::PRODUCT_PRICING,
                                            ];
                                            // New Envelope is used for each feed.
                                            $envelope = $this->prepare($specifics, null);
                                            $this->feed->send($envelope, $specifics);
                                            $status = true;
                                        }
                                    } else {
                                        // 2. Same price for different countries
                                        $specifics = [
                                            'ids' => $productIds,
                                            'account_id' => $accountId,
                                            'marketplace' => $profile->getMarketplace(),
                                            'profile_id' => $profileId,
                                            'store_id' => $storeId,
                                            'type' => \Amazon\Sdk\Api\Feed::PRODUCT_PRICING,
                                        ];

                                        $envelope = $this->prepare($specifics, $envelope);
                                        $this->feed->send($envelope, $specifics);
                                        $status = true;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $status;
    }

    /**
     * Prepare Price for Amazon
     * @param array $specifics
     * @param array|null $envelope
     * @return \Amazon\Sdk\Envelope|null
     * @throws \Exception
     */
    public function prepare(array $specifics = [], $envelope = null)
    {
        if (isset($specifics) && !empty($specifics)) {
            $sale = $this->config->getAllowSalePrice();

            $ids = $specifics['ids'];
            /** @var \Ced\Amazon\Api\Data\ProfileInterface $profile */
            $profile = $this->profile->getById($specifics['profile_id']);

            /** @var \Magento\Store\Api\Data\StoreInterface $store */
            $store = $profile->getStore();

            $currency = $store->getCurrentCurrencyCode();

            /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
            $account = $this->account->getById($specifics['account_id']);

            if (!isset($envelope)) {
                /** @var \Amazon\Sdk\Envelope $envelope */
                $envelope = $this->envelope->create(
                    [
                        'merchantIdentifier' => $account->getConfig($profile->getMarketplaceIds())->getSellerId(),
                        'messageType' => \Amazon\Sdk\Base::MESSAGE_TYPE_PRICE
                    ]
                );
            }

            $attributeList = $this->config->getPriceAttributeList();
            $attributeList = array_merge(
                $attributeList,
                ['sku', 'entity_id', 'type_id', 'price', 'special_price', 'special_from_date', 'special_to_date']
            );

            /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $products */
            $products = $this->products->create()
                ->setStoreId($store->getId())
                ->addAttributeToSelect($attributeList)
                ->addAttributeToFilter('entity_id', ['in' => $ids]);
            /** @var \Magento\Catalog\Model\Product $product */
            foreach ($products as $product) {
                // case 1 : for configurable products
                if ($product->getTypeId() == 'configurable') {
                    $parentId = $product->getId();
                    $productType = $product->getTypeInstance();

                    /** @codingStandardsIgnoreStart */
                    $childIds = $productType->getChildrenIds($parentId);
                    /** @codingStandardsIgnoreEnd */
                    /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $childs */
                    $childs = $this->products->create()
                        ->setStoreId($store->getId())
                        ->addAttributeToSelect($attributeList)
                        ->addAttributeToFilter('entity_id', ['in' => $childIds[0]]);
                    /** @var \Magento\Catalog\Model\Product $child */
                    foreach ($childs as $child) {
                        /** @var array $value */
                        $value = $this->calculate($child, $specifics);

                        /** @var \Amazon\Sdk\Product\Price $price */
                        $price = $this->price->create();
                        $price->setId($profile->getId() . $child->getId());
                        $price->setData($child->getSku(), $value['SalePrice'], $currency);

                        if ($sale) {
                            $price->setData($child->getSku(), $value['StandardPrice'], $currency);
                            $from = $child->getData('special_from_date');
                            $to = $child->getData('special_to_date');
                            $price->setSale($value['SalePrice'], $from, $to, $currency);
                        }

                        $envelope->addPrice($price);
                    }
                } elseif ($product->getTypeId() == 'simple') {
                    // case 2 : for simple products

                    /** @var array $value */
                    $value = $this->calculate($product, $specifics);

                    /** @var \Amazon\Sdk\Product\Price $price */
                    $price = $this->price->create();
                    $price->setId($profile->getId() . $product->getId());
                    $price->setData($product->getSku(), $value['SalePrice'], $currency);

                    if ($sale) {
                        $price->setData($product->getSku(), $value['StandardPrice'], $currency);
                        $from = $product->getData('special_from_date');
                        $to = $product->getData('special_to_date');
                        $price->setSale($value['SalePrice'], $from, $to, $currency);
                    }

                    $envelope->addPrice($price);
                }
            }
        }

        return $envelope;
    }

    /**
     * TODO: add number formating for comma and dot separators.
     * Calculate price on the basis of global config
     * @param \Magento\Catalog\Model\Product $product
     * @param array $specifics
     * @return array
     */
    public function calculate($product, $specifics)
    {
        // FinalPrice is always the lower of special_price and price.
        $splprice = (float)str_replace(',', '', $product->getFinalPrice());
        $price = (float)str_replace(',', '', $product->getPrice());
        $type = $this->config->getPriceType();
        switch ($type) {
            case \Ced\Amazon\Model\Source\Config\Price::TYPE_FIXED_INCREASE:
                $fixed = $this->config->getPriceFixed();
                $splprice = $this->calculateFixed(
                    $splprice,
                    $fixed,
                    \Ced\Amazon\Model\Source\Config\Price::TYPE_FIXED_INCREASE
                );
                $price = $this->calculateFixed(
                    $price,
                    $fixed,
                    \Ced\Amazon\Model\Source\Config\Price::TYPE_FIXED_INCREASE
                );
                break;

            case \Ced\Amazon\Model\Source\Config\Price::TYPE_FIXED_DECREASE:
                $fixed = $this->config->getPriceFixed();
                $price = $this->calculateFixed(
                    $price,
                    $fixed,
                    \Ced\Amazon\Model\Source\Config\Price::TYPE_FIXED_DECREASE
                );
                $splprice = $this->calculateFixed(
                    $splprice,
                    $fixed,
                    \Ced\Amazon\Model\Source\Config\Price::TYPE_FIXED_DECREASE
                );
                break;

            case \Ced\Amazon\Model\Source\Config\Price::TYPE_PERCENTAGE_INCREASE:
                $percentage = $this->config->getPricePercentage();
                $price = $this->calculatePercentage(
                    $price,
                    $percentage,
                    \Ced\Amazon\Model\Source\Config\Price::TYPE_PERCENTAGE_INCREASE
                );
                $splprice = $this->calculatePercentage(
                    $splprice,
                    $percentage,
                    \Ced\Amazon\Model\Source\Config\Price::TYPE_PERCENTAGE_INCREASE
                );
                break;

            case \Ced\Amazon\Model\Source\Config\Price::TYPE_PERCENTAGE_DECREASE:
                $percentage = $this->config->getPricePercentage();
                $price = $this->calculatePercentage(
                    $price,
                    $percentage,
                    \Ced\Amazon\Model\Source\Config\Price::TYPE_PERCENTAGE_DECREASE
                );
                $splprice = $this->calculatePercentage(
                    $splprice,
                    $percentage,
                    \Ced\Amazon\Model\Source\Config\Price::TYPE_PERCENTAGE_DECREASE
                );
                break;

            case \Ced\Amazon\Model\Source\Config\Price::TYPE_ATTRIBUTE:
                $mappings = $this->config->getPriceAttribute();
                $marketplaceIds = isset($specifics['marketplace']) ?
                    explode(',', $specifics['marketplace']) : [];
                try {
                    foreach ($marketplaceIds as $marketplaceId) {
                        if (isset($mappings[$marketplaceId]) && !empty($mappings[$marketplaceId])) {
                            $custom = (float)str_replace(',', '', $product->getData($mappings[$marketplaceId]));

                            break;
                        }
                    }
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage(), ['path' => __METHOD__]);
                }

                $price = (empty($custom) || $custom == 0.00) ? $price : $custom;
                $splprice = $price;
                break;
        }

        $splprice = round((float)$splprice, 2);
        $price = round((float)$price, 2);

        if ((empty($splprice) || $splprice == 0.00) && (!empty($price) || $price != 0.00)) {
            $splprice = $price;
        }

        if ((empty($price) || $price == 0.00) && (!empty($splprice) || $splprice != 0.00)) {
            $price = $splprice;
        }

        // StandardPrice > SalePrice
        $response = [
            'StandardPrice' => (string)$price,
            'SalePrice' => (string)$splprice,
        ];

        if ($splprice < $price) {
            $response = [
                'StandardPrice' => (string)$price,
                'SalePrice' => (string)$splprice,
            ];
        } elseif ($price < $splprice) {
            $response = [
                'StandardPrice' => (string)$splprice,
                'SalePrice' => (string)$price,
            ];
        }

        return $response;
    }

    /**
     * ForFixPrice
     * @param null $price
     * @param null $fixed
     * @param string $type
     * @return float|null
     */
    public function calculateFixed(
        $price = null,
        $fixed = null,
        $type = \Ced\Amazon\Model\Source\Config\Price::TYPE_FIXED_DECREASE
    ) {
        if (is_numeric($fixed) && ($fixed != '')) {
            $fixed = (float)$fixed;
            if ($fixed > 0) {
                $price = $type == \Ced\Amazon\Model\Source\Config\Price::TYPE_FIXED_DECREASE ?
                    (float)($price + $fixed) : (float)($price - $fixed);
            }
        }
        return $price;
    }

    /**
     * ForPerPrice
     * @param null $price
     * @param null $percentage
     * @param string $type
     * @return float|null
     */
    public function calculatePercentage(
        $price = null,
        $percentage = null,
        $type = \Ced\Amazon\Model\Source\Config\Price::TYPE_PERCENTAGE_INCREASE
    ) {
        if (is_numeric($percentage)) {
            $percentage = (float)$percentage;
            if ($percentage > 0) {
                $price = $type == \Ced\Amazon\Model\Source\Config\Price::TYPE_PERCENTAGE_INCREASE ?
                    (float)($price + (($price / 100) * $percentage))
                    : (float)($price - (($price / 100) * $percentage));
            }
        }
        return $price;
    }

    /**
     * Push item in queue
     * @param \Ced\Amazon\Api\Data\ProfileInterface $profile
     * @param string $marketplace
     * @param array $ids
     * @param string $priority
     * @return bool
     */
    private function push(
        $profile,
        $marketplace,
        $ids,
        $priority = \Ced\Amazon\Model\Source\Queue\Priorty::MEDIUM
    ) {
        $status = false;
        try {
            // queue
            $productIds = $this->profile
                ->getAssociatedProductIds($profile->getId(), $profile->getStoreId(), $ids);
            $specifics = [
                'ids' => $productIds,
                'account_id' => $profile->getAccountId(),
                'marketplace' => $marketplace,
                'profile_id' => $profile->getId(),
                'store_id' => $profile->getStoreId(),
                'type' => \Amazon\Sdk\Api\Feed::PRODUCT_PRICING,
            ];
            /** @var \Ced\Amazon\Api\Data\Queue\DataInterface $queueData */
            $queueData = $this->queueDataFactory->create();
            $queueData->setAccountId($profile->getAccountId());
            $queueData->setMarketplace($marketplace);
            $queueData->setSpecifics($specifics);
            $queueData->setType(\Amazon\Sdk\Api\Feed::PRODUCT_PRICING);
            $queueData->setPriorty($priority);
            $status = $this->queue->push($queueData);
        } catch (\Exception $e) {
            $this->logger->error(
                "Amazon Cron : All price failed.",
                [
                    'status' => $status,
                    'count' => count($productIds),
                    'exception' => $e->getMessage()
                ]
            );
        }
        return $status;
    }
}
