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
 * @package     RueDuCommerce-Sdk
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace RueDuCommerceSdk;

interface SandboxInterface
{
    /**
     * Create Purchase Order on RueDuCommerce Sandbox
     * @param string $sellerId
     * @param string $itemId
     * @param array $params = [
     * 'multipleLinesFlag' => 'true',
     * 'poDate' => '2015-06-24',
     * 'shippingAddressName' => 'John Doe',
     * 'shippingAddressLine1' => '123 Ave',
     * 'shippingAddressCity' => 'San Franscisco',
     * 'shippingAddressState' => 'CA',
     * 'shippingAddressPostalCode' => '94002',
     * ]
     * @return mixed
     */
    public function createOrder($sellerId = '', $itemId = '', $params = []);
}
