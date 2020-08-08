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
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\ResourceModel;

/**
 * Class Profile
 * @package Ced\Amazon\Model\ResourceModel
 * @method load(\Ced\Amazon\Api\Data\ProfileInterface $object, $value, $field = null)
 * @method save(\Ced\Amazon\Api\Data\ProfileInterface $object)
 */
class Profile extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE_NAME_CATALOG_PRODUCT_ENTITY = "catalog_product_entity";
    const TABLE_NAME_CATALOG_PRODUCT_VALUE = "catalog_product_entity_varchar";

    public $storeIds;

    public $attibuteId;

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Ced\Amazon\Model\Profile::NAME, \Ced\Amazon\Model\Profile::COLUMN_ID);
    }

    /**
     * Get distinct profile ids by product ids
     * @param array $productIds
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function getProfileIdsByProductIds(array $productIds = [])
    {
        $profileIds = [];
        if (!empty($productIds) && is_array($productIds)) {
            /** @var \Magento\Framework\App\ProductMetadataInterface $meta */
            $meta = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\App\ProductMetadataInterface::class);
            $edition = $meta->getEdition();
            $attributeIdField = 'entity_id';
            if ($edition == 'Enterprise') {
                $attributeIdField = 'row_id';
            }

            $productIds = implode(',', $productIds);
            $storeIds = implode(',', $this->getStoreIds());
            $attributeId = $this->getAttributeId(\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PROFILE_ID);
            $mainTable = $this->getTable(self::TABLE_NAME_CATALOG_PRODUCT_ENTITY);
            $attrTable = $this->getTable(self::TABLE_NAME_CATALOG_PRODUCT_VALUE);
            $query = $this->getConnection()->select()
                ->from(
                    ['main_table' => $mainTable],
                    [
                        'main_table.entity_id',
                        'attr.value',
                        'attr.store_id'
                    ]
                )
                ->where(
                    "attr.attribute_id = {$attributeId} AND main_table.entity_id IN ({$productIds}) AND attr.store_id IN ({$storeIds}) AND attr.value IS NOT NULL "
                );
            $query->group("attr.value");
            $query->joinLeft(
                ['attr' => $attrTable],
                "main_table.{$attributeIdField} = attr.{$attributeIdField}"
            );

            $result = $this->getConnection()->query($query);
            $products = $result->fetchAll();
            foreach ($products as $value) {
                $profileIds[$value['store_id']][$value['value']] = $value['value'];
            }
        }

        return $profileIds;
    }

    /**
     * NOTE: It does not support flat tables.
     * Get profile ids by product id
     * @param $productId
     * @param array $storeIds
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function getByProductId($productId, $storeIds = [0])
    {
        /** @var \Magento\Framework\App\ProductMetadataInterface $meta */
        $meta = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\App\ProductMetadataInterface::class);
        $edition = $meta->getEdition();
        $attributeIdField = 'entity_id';
        if ($edition == 'Enterprise') {
            $attributeIdField = 'row_id';
        }

        $profileIds = [];
        $attributeId = $this->getAttributeId(\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PROFILE_ID);
        $mainTable = $this->getTable(self::TABLE_NAME_CATALOG_PRODUCT_ENTITY);
        $attrTable = $this->getTable(self::TABLE_NAME_CATALOG_PRODUCT_VALUE);
        $query = $this->getConnection()->select()
            ->from(
                ['main_table' => $mainTable],
                [
                    'main_table.entity_id',
                    'attr.value',
                    'attr.store_id'
                ]
            )
            ->where(
                "main_table.entity_id = {$productId} AND attr.attribute_id = {$attributeId} AND attr.store_id IN ("
                .implode(',', $storeIds).") AND attr.value IS NOT NULL  AND attr.value != 'na'"
            );
        $query->joinLeft(
            ['attr'=> $attrTable],
            "main_table.{$attributeIdField} = attr.{$attributeIdField}"
        );

        $result = $this->getConnection()->query($query);
        $product = $result->fetchAll();
        foreach ($product as $value) {
            $profileIds[$value['store_id']] = $value['value'];
        }

        return $profileIds;
    }

    /**
     * Get attribute id by attribute code, for amazon_profile_id attribute
     * @param $code
     * @return mixed
     * @throws \Zend_Db_Statement_Exception
     */
    public function getAttributeId($code)
    {
        if (!isset($this->attibuteId)) {
            $mainTable = $this->getTable('eav_attribute');
            $query = $this->getConnection()->select()
                ->from($mainTable, ['attribute_id'])
                ->where("attribute_code = '{$code}'");
            $attributes = $this->getConnection()->query($query)->fetchAll();
            foreach ($attributes as $attribute) {
                if (isset($attribute['attribute_id'])) {
                    $this->attibuteId = $attribute['attribute_id'];
                }
            }
        }

        return $this->attibuteId;
    }

    /**
     * Get store ids used in profiles
     * TODO: set for active profile only
     * @return mixed
     * @throws \Zend_Db_Statement_Exception
     */
    public function getStoreIds()
    {
        if (!isset($this->storeIds)) {
            $mainTable = $this->getTable(\Ced\Amazon\Model\Profile::NAME);
            $query = $this->getConnection()->select()
                ->from($mainTable, ['store_id'])
                ->distinct(true);
            $result = $this->getConnection()->query($query);
            $profiles = $result->fetchAll();

            $useDefault = false;
            /** @var \Magento\Store\Model\StoreManager $storeManager */
            $storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Store\Model\StoreManagerInterface::class);
            if ($storeManager->hasSingleStore()) {
                $useDefault = true;
            }

            foreach ($profiles as $profile) {
                if (isset($profile['store_id'])) {
                    $storeId = $useDefault ? \Magento\Store\Model\Store::DEFAULT_STORE_ID :
                        $profile['store_id'];
                    $this->storeIds[$storeId] = $storeId;
                }
            }

            if (empty($this->storeIds)) {
                $this->storeIds[0] = 0;
            }
        }

        return $this->storeIds;
    }
}
