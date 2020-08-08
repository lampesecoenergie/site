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
 * @package     Ced_2.3
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Api\Service;

interface ProductServiceInterface
{
    /**
     * Find Product By SKU
     * @param $sku
     * @return bool|\Magento\Catalog\Api\Data\ProductInterface
     */
    public function find($sku);

    /**
     * Create Product
     * @param null|\Amazon\Sdk\Api\Order\ItemList $items
     * @param null|integer $index
     * @return \Magento\Catalog\Api\Data\ProductInterface|boolean
     */
    public function create($items = null, $index = null);

    /**
     * Update Product Attributes
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param mixed $attributes
     * @throws \Exception
     */
    public function update($product, array $attributes = []);
}
