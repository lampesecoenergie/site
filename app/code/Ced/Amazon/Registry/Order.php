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
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Registry;

class Order
{
    private $order;

    private $items;

    private $countryCode;

    public function clear()
    {
        $this->order = null;
        $this->items = null;
        $this->countryCode = null;
    }

    public function setCountryCode($code)
    {
        $this->countryCode = $code;
    }

    public function getCountryCode()
    {
        return $this->countryCode;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * Get Amazon Order Items
     * @return \Amazon\Sdk\Api\Order\ItemList|null
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Is Amazon Order
     * @return bool
     */
    public function hasOrder()
    {
        $order = $this->getOrder();
        if (isset($order)) {
            return true;
        }

        return false;
    }

    /**
     * Is Item Exists
     * @param $sku
     * @return bool
     */
    public function hasItem($sku)
    {
        $items = $this->getItems();
        if (isset($items)) {
            foreach ($items as $index => $item) {
                if ($items->getSellerSKU($index) == $sku) {
                    return true;
                }
            }
        }

        return false;
    }
}
