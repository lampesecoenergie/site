<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 19/2/18
 * Time: 12:27 PM
 */

namespace Ced\Cdiscount\Model;


class Categories extends \Magento\Framework\Model\AbstractModel
{

    public function _construct()
    {
        $this->_init('Ced\Cdiscount\Model\ResourceModel\Category');
    }

    public function getEanOptional($categoryCode)
    {
        $eanOptional = false;
        if (isset($categoryCode['category'])) {
            $eanOptional = $this->getCollection()->addFieldToFilter('code', ['eq' => $categoryCode['category']])
                ->getFirstItem()->getData('ean_optional');
        }
        return $eanOptional;
    }

    public function getSimpleAllowed($categoryCode)
    {
        $simpleAllowed = false;
        if (isset($categoryCode['category'])) {
            $simpleAllowed = $this->getCollection()->addFieldToFilter('code', ['eq' => $categoryCode['category']])
                ->getFirstItem()->getData('is_simple_allowed');
        }
        return $simpleAllowed;
    }

    public function getVariantAllowed($categoryCode)
    {
        $variantAllowed = false;
        if (isset($categoryCode['category'])) {
            $variantAllowed = $this->getCollection()->addFieldToFilter('code', ['eq' => $categoryCode['category']])
                ->getFirstItem()
                ->getData('is_variant_allowed');
        }
        return $variantAllowed;
    }


}