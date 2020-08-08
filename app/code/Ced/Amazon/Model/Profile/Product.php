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

namespace Ced\Amazon\Model\Profile;

use Ced\Amazon\Model\Cache;

/**
 * Class Product
 * @package Ced\Amazon\Model\Profile
 */
class Product implements \Ced\Integrator\Model\Profile\ProductInterface
{
    const CACHE_KEY_PROFILE_PRODUCT = "profile_product_";
    const PROFILE_VALUE_NA = "na";

    /** @var \Magento\Catalog\Model\Product\ActionFactory */
    public $action;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
    public $catalog;

    /** @var \Ced\Amazon\Model\Cache */
    public $cache;

    /** @var array */
    public $pool;

    /** @var \Magento\Store\Model\StoreManagerInterface  */
    public $storeManager;

    /** @var \Ced\Amazon\Model\ResourceModel\Profile  */
    public $profileResource;

    /**
     * Product constructor.
     * @param Cache $cache
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\ActionFactory $actionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalog
     * @param \Ced\Amazon\Model\ResourceModel\Profile $profileResource
     */
    public function __construct(
        \Ced\Amazon\Model\Cache $cache,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\ActionFactory $actionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalog,
        \Ced\Amazon\Model\ResourceModel\Profile $profileResource
    ) {
        $this->cache = $cache;
        $this->storeManager = $storeManager;
        $this->action = $actionFactory;
        $this->catalog = $catalog;
        $this->profileResource = $profileResource;
    }

    /**
     * @param null $profileId
     * @param int $storeId
     * @param array $ids
     */
    public function remove($profileId = null, $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID, array $ids = [])
    {
        \Magento\Framework\Profiler::start('amazon-profile-product-remove');
        if (!empty($profileId)) {
            if ($this->storeManager->hasSingleStore()) {
                $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            }

            if (!empty($ids)) {
                $this->action->create()
                    ->updateAttributes(
                        $ids,
                        [\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PROFILE_ID => null],
                        $storeId
                    );
            }

            if (isset($this->pool[$storeId][$profileId])) {
                unset($this->pool[$storeId][$profileId]);
            }

            $this->cache->removeValue(self::CACHE_KEY_PROFILE_PRODUCT . $storeId . "_" . $profileId);
        }

        \Magento\Framework\Profiler::stop('amazon-profile-product-remove');
    }

    /**
     * Purge Cache
     * @param $storeId
     * @param $profileId
     */
    public function purge($storeId, $profileId)
    {
        if (isset($this->pool[$storeId][$profileId])) {
            unset($this->pool[$storeId][$profileId]);
        }

        $this->cache->removeValue(self::CACHE_KEY_PROFILE_PRODUCT . $storeId . "_" . $profileId);
    }

    /**
     * Get Profile Product Ids
     * @param $profileId
     * @param int $storeId
     * @return array
     * @throws \Exception
     */
    public function getIds($profileId, $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID)
    {
        \Magento\Framework\Profiler::start('amazon-profile-product-get-ids');
        if ($this->storeManager->hasSingleStore()) {
            $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        if (isset($this->pool[$storeId][$profileId]) && is_array($this->pool[$storeId][$profileId])) {
            $ids = $this->pool[$storeId][$profileId];
        } else {
            $ids = $this->cache->getValue(self::CACHE_KEY_PROFILE_PRODUCT . $storeId . "_" . $profileId);
            if (!isset($ids)) {
                $ids = $this->get($profileId, $storeId)->getAllIds();
                $this->cache->setValue(self::CACHE_KEY_PROFILE_PRODUCT . $storeId . "_" . $profileId, $ids);
            }

            $this->pool[$storeId][$profileId] = $ids;
        }

        \Magento\Framework\Profiler::stop('amazon-profile-product-get-ids');

        return $ids;
    }

    /**
     * Get Size
     * TODO: Optimize
     * @param $profileId
     * @param int $storeId
     * @return int|void
     * @throws \Exception
     */
    public function getSize($profileId, $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID)
    {
        \Magento\Framework\Profiler::start('amazon-profile-product-get-size');

        $size = count($this->getIds($profileId, $storeId));

        \Magento\Framework\Profiler::stop('amazon-profile-product-get-size');

        return $size;
    }

    /**
     * @param $profileId
     * @param int $storeId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function get($profileId, $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID)
    {
        \Magento\Framework\Profiler::start('amazon-profile-product-get');

        if ($this->storeManager->hasSingleStore()) {
            $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->catalog->create();
        $collection
            ->setStoreId($storeId)
            ->addFieldToFilter(
                \Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PROFILE_ID,
                [
                    'eq' => $profileId
                ]
            )
            ->addFieldToFilter(
                \Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PROFILE_ID,
                [
                    'neq' => self::PROFILE_VALUE_NA
                ]
            );
        \Magento\Framework\Profiler::stop('amazon-profile-product-get');

        return $collection;
    }

    /**
     * Note: In case of single store, values are set only on admin store ( i.e. 0 store)
     * Update profile id to product ids
     * @param null $profileId
     * @param int $storeId
     * @param array $ids
     * @throws \Exception
     */
    public function add($profileId = null, $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID, $ids = [])
    {
        \Magento\Framework\Profiler::start('amazon-profile-product-add');

        if ($this->storeManager->hasSingleStore()) {
            $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        } else {
            // Updating values to Admin store, for issue regarding inner join as admin store is used for default values.
            $this->action->create()
                ->updateAttributes(
                    $ids,
                    [\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PROFILE_ID => self::PROFILE_VALUE_NA],
                    \Magento\Store\Model\Store::DEFAULT_STORE_ID
                );
        }

        // Updating selected store
        $this->action->create()
            ->updateAttributes(
                $ids,
                [\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PROFILE_ID => $profileId],
                $storeId
            );

        if (isset($this->pool[$storeId][$profileId])) {
            unset($this->pool[$storeId][$profileId]);
        }

        $this->cache->removeValue(self::CACHE_KEY_PROFILE_PRODUCT . $storeId . "_" . $profileId);

        $this->getIds($profileId, $storeId);

        \Magento\Framework\Profiler::stop('amazon-profile-product-add');
    }

    /**
     * Check if Marketplace Product
     * @param $productId
     * @param $storeId
     * @return bool
     */
    public function isMarketplaceProduct($productId, $storeId)
    {
        try {
            $storeIds = $this->profileResource->getStoreIds();
            return (0 < count($this->profileResource->getByProductId($productId, $storeIds)));
        } catch (\Exception $e) {
            return false;
        }
    }
}
