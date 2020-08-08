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
 * @package     Ced_EbayMultiAccount
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\EbayMultiAccount\Model;

class Productchange extends \Magento\Framework\Model\AbstractModel
{

    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    const CRON_TYPE_INVENTORY = 'inventory';
    const CRON_TYPE_PRICE = 'price';
    /**
     * @var string
     */
    protected $_eventPrefix = 'ebaymultiaccount_productchange';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('Ced\EbayMultiAccount\Model\ResourceModel\Productchange');
    }

    public function deleteFromProductChange($productIds, $type)
    {
        $this->_getResource()->deleteFromProductChange($productIds, $type);
        return $this;
    }

    public function setProductChange($productId, $oldValue='', $newValue='', $type){
        if ($productId <= 0) {
            return $this;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper
         */
        $multiAccountHelper = $objectManager->create('\Ced\EbayMultiAccount\Helper\MultiAccount');

        $isEbayProduct = '';
        $parentFound = false;
        $profileAttrs = $multiAccountHelper->getAllProfileAttr();

        $product = $objectManager->create('\Magento\Catalog\Model\Product')->load($productId);

        foreach ($profileAttrs as $profileAttrCode) {
            $isEbayProduct = $product->getData($profileAttrCode);
            if($isEbayProduct != '') {
                break;
            }
        }
        $checkForChild = $objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')->getParentIdsByChild($product->getId());
        if($isEbayProduct == null && count($checkForChild) > 0) {
            foreach ($checkForChild as $childParentId) {
                $product = $objectManager->create('\Magento\Catalog\Model\Product')
                    ->load($childParentId);
                foreach ($profileAttrs as $profileAttrCode) {
                    $isEbayProduct = $product->getData($profileAttrCode);
                    if($isEbayProduct != '') {
                        $parentFound = true;
                        break;
                    }
                }
                if($parentFound) {
                    break;
                }
            }
        }


        if ($product && $isEbayProduct != '') {
            $collection = $this->getCollection()->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('cron_type', $type);

            if (count($collection) > 0) {
                $this->load($collection->getFirstItem()->getId());
                if($oldValue == '') {
                    $oldValue = $collection->getFirstItem()->getOldValue();
                }
            } else {
                $this->setProductId($productId);
            }

            $this->setOldValue($oldValue);
            $this->setNewValue($newValue);
            $this->setAction(self::ACTION_UPDATE);
            $this->setCronType($type);
            $this->save();
        }
        return $this;
    }
}