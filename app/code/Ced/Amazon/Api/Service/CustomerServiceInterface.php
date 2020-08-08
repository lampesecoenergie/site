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

interface CustomerServiceInterface
{
    /**
     * Get or Create Customer
     * @param \Amazon\Sdk\Api\Order $order
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param string|null $groupId
     * @return null|\Magento\Customer\Api\Data\CustomerInterface
     */
    public function get($order, $store, $groupId = null);
}
