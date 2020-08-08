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
 * @category  Ced
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\Cdiscount\Model;

class Profile extends \Magento\Framework\Model\AbstractModel
{
    const ATTRIBUTE_CODE_PROFILE_ID = 'cdiscount_profile_id';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public $product;

    public $action;

    public $category;

    /**
     * Profile products flipped
     * @var array
     */
    public $productIds = [];

    public $objectManager;

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('Ced\Cdiscount\Model\ResourceModel\Profile');
    }

    //TODO: remove as not needed.
    public function getProductsReadonly()
    {
        return [];
    }

    //@TODO: impliment without object manager
    public function getProductsPosition()
    {
        if (!isset($this->objectManager)) {
            $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        }

        if (!isset($this->product)) {
            $this->product = $this->objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        }

        $id = $this->getId();
        $ids = $this->product->create()->addAttributeToFilter('cdiscount_profile_id', ['eq' => $id])
            ->addAttributeToSelect('entity_id')->getAllIds();
        if (is_array($ids) and !empty($ids)) {
            $this->productIds = array_flip($ids);
        }

        return $this->productIds;
    }

    public function removeProducts($storeId)
    {
        if (!isset($this->objectManager)) {
            $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        }

        try {
            $filters = $this->getProfileProductsFilters();

            if (!empty($filters)) {

                if (!isset($this->product)) {
                    $this->product = $this->objectManager
                        ->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
                }
                $id = $this->getId();
                $products = $this->product->create()
                    ->setStore($storeId)
                    ->addAttributeToFilter('cdiscount_profile_id', ['eq' => $id])
                    ->addAttributeToSelect('cdiscount_profile_id');
                $productIds = $products->getAllIds();
                $this->action = $this->objectManager->create('\Magento\Catalog\Model\Product\ActionFactory');
                // Removing profile id from already added products
                $this->action->create()->updateAttributes($productIds, [self::ATTRIBUTE_CODE_PROFILE_ID => null], $storeId);
            }
        } catch (\Exception $e) {
            $config = $this->objectManager->create('\Ced\Cdiscount\Helper\Config');
            if ($config->getDebugMode() == true) {
                $config->logger->error($e->getMessage(), ['path' => __METHOD__, 'trace' => $e->getTraceAsString()]);
            }
        }
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addProducts($storeId)
    {
        try {
            if (!isset($this->objectManager)) {
                $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            }

            if (!isset($this->product)) {
                $this->product = $this->objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
            }

            $id = $this->getId();

            $this->category = $this->objectManager->create('\Magento\Catalog\Model\CategoryFactory');

            $filters = $this->parseFilters($this->getProfileProductsFilters());
            if (!empty($filters)) {
                $products = $this->product->create()
                    ->setStoreId($storeId)
                    // ->addAttributeToFilter('cdiscount_profile_id', ['neq' => $id])
                    ->addAttributeToSelect('cdiscount_profile_id');

                // Applying grid filters
                foreach ($filters as $field => $value) {
                    if ($field == 'category_ids') {
                        $categoryFilter = $this->category->create()->load($value);
                        $products->addCategoryFilter($categoryFilter);
                    } elseif (in_array($field, ['sku', 'name'])) {
                        $products->addAttributeToFilter($field, ['like' => '%' . str_replace('%20', ' ', $value) . '%']);
                    } else {
                        $products->addAttributeToFilter($field, ['eq' => $value]);
                    }
                }

                //print_r($products->getAllIds());
                // Writing profile id
                $this->action = $this->objectManager->create('\Magento\Catalog\Model\Product\ActionFactory');
                $out = $this->action->create()->updateAttributes($products->getAllIds(), [self::ATTRIBUTE_CODE_PROFILE_ID => $id], $storeId);
                //print_r($out->getData());die();
                $output = null;
                set_time_limit(0);
                exec("php bin/magento cron:run" , $output);
            }
        } catch (\Exception $e) {
            $config = $this->objectManager->create('\Ced\Cdiscount\Helper\Config');
            if ($config->getDebugMode() == true) {
                $config->logger->error($e->getMessage(), ['path' => __METHOD__, 'trace' => $e->getTraceAsString()]);
            }
        }

    }

    //@TODO: handle all types of filters, define fixed filter attribute.
    public function parseFilters($filters)
    {
        $parsedFilter = [];
        if (!empty($filters)) {
            $filtersArray = explode('&', trim($filters));
            foreach ($filtersArray as $item) {
                $filtersValue = explode('=', $item);
                if (isset($filtersValue[0], $filtersValue[1])) {
                    $parsedFilter[trim($filtersValue[0])] = trim($filtersValue[1]);
                }
            }
        }
        return $parsedFilter;
    }

    /**
     * @param $field
     * @param $value
     * @param string $additionalAttributes
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByField($field, $value, $additionalAttributes = '*')
    {
        $collection = $this->getResourceCollection()->addFieldToSelect($additionalAttributes);
        if (is_array($field) && is_array($value)) {
            foreach ($field as $key => $f) {
                if (isset($value[$key])) {
                    //$f = $helper->getTableKey($f);
                    $collection->addFieldToFilter($f, $value[$key]);
                }
            }
        } else {
            /* echo "{{".$field.' == '.$value."}}"; */
            //$field = $helper->getTableKey($field);
            $collection->addFieldToFilter($field, $value);
            /*  */
        }

        $collection->setCurPage(1)
            ->setPageSize(1);
        foreach ($collection as $object) {
            $this->load($object->getId());
            return $this;
        }
        return $this;
    }
}
