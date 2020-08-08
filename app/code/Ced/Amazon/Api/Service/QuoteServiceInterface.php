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

interface QuoteServiceInterface
{
    /**
     * Set Quote Store
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @return void
     */
    public function setStore($store);

    /**
     * Set Quote Account
     * @param \Ced\Amazon\Api\Data\AccountInterface $account
     * @return void
     */
    public function setAccount($account);

    /**
     * Set Quote Customer
     * @param \Magento\Customer\Api\Data\CustomerInterface|null $customer
     * @return void
     */
    public function setCustomer($customer);

    /**
     * Set US regions
     * @param array $regions
     * @return void
     */
    public function setRegions($regions);

    /**
     * Get Quote Store
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore();

    /**
     * Get Quote Account
     * @return \Ced\Amazon\Api\Data\AccountInterface
     */
    public function getAccount();

    /**
     * Get Quote Customer
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getCustomer();

    /**
     * Create Quote
     * @param \Amazon\Sdk\Api\Order|null $order
     * @return int
     */
    public function create($order = null);
}
