<?php
/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Magetop_Productslider
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

namespace Magetop\Productslider\Block;

/**
 * Class CustomProducts
 * @package Magetop\Productslider\Block
 */
class CustomProducts extends AbstractSlider
{
    /**
     * @return $this|mixed
     */
    public function getProductCollection()
    {
        $productIds = $this->getSlider()->getProductIds();
        if (!is_array($productIds)) {
            $productIds = explode('&', $productIds);
        }

        $collection = $this->_productCollectionFactory->create()
            ->addIdFilter($productIds)
            ->setPageSize($this->getProductsCount());
        $this->_addProductAttributesAndPrices($collection);

        return $collection;
    }
}