<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Feed\Export\Resolver\Product;

use Magento\Catalog\Model\Product;
use Magento\Tax\Model\Calculation as TaxCalculation;
use Magento\Catalog\Helper\Data   as TaxHelper;

class PriceResolver extends AbstractResolver
{
    /**
     * @var TaxCalculation
     */
    private $taxCalculation;

    public function __construct(
        TaxCalculation $taxCalculation,
        TaxHelper $taxHelper
    ) {
        $this->taxCalculation = $taxCalculation;
        $this->taxHelper = $taxHelper;
    }

    /**
     * Price
     *
     * @param Product $product
     * @return float
     */
    public function getPrice($product)
    {
        return $product->getPrice();
    }

    /**
     * Final Price
     *
     * @param Product $product
     * @return float
     */
    public function getRegularPrice($product)
    {
        return $product->getPriceInfo()->getPrice('regular_price')->getValue();
    }

    /**
     * Final Price
     *
     * @param Product $product
     * @return float
     */
    public function getSpecialPrice($product)
    {
        return $product->getPriceInfo()->getPrice('special_price')->getValue();
    }

    /**
     * Final Price
     *
     * @param Product $product
     * @return float
     */
    public function getFinalPrice($product)
    {
        return $product->getPriceInfo()->getPrice('final_price')->getValue();
    }

    /**
     * Final Price with Tax
     *
     * @param Product $product
     * @return float
     */
    public function getFinalPriceTax($product)
    {
        return $this->taxHelper->getTaxPrice($product, $this->getFinalPrice($product), true);
    }

    /**
     * Tax Rate
     *
     * @param Product $product
     * @return float
     */
    public function getTaxRate($product)
    {
        if ($this->getFeed()) {
            $storeId = $this->getFeed()->getStoreId();
        } else {
            $storeId = 0;
        }

        $request = $this->taxCalculation->getRateRequest(null, null, null, $storeId);
        $request->setData('product_class_id', $product->getTaxClassId());

        return $this->taxCalculation->getRate($request);
    }
}