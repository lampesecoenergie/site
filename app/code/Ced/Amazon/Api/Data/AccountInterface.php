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
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Api\Data;

/**
 * Interface AccountInterface
 * @package Ced\Amazon\Api\Data
 * @api
 */
interface AccountInterface extends \Ced\Integrator\Api\Data\AccountInterface
{
    /**
     * Get Order Channel (AFN/MFN).
     * @return string
     */
    public function getChannel();

    /**
     * Set Order Channel (AFN/MFN)
     * @param string $channel
     * @return $this
     */
    public function setChannel($channel);

    /**
     * Get all marketplace ids
     * @return mixed
     */
    public function getMarketplaceIds();

    /**
     * Get API config object
     * @param $marketplaceIds
     * @return \Amazon\Sdk\Api\Config
     */
    public function getConfig($marketplaceIds = []);

    /**
     * Get Shipping Method
     * @return string
     */
    public function getShippingMethod();

    /**
     * Set Shipping Method
     * @param string $method
     * @return $this
     */
    public function setShippingMethod($method);

    /**
     * Get Payment Method
     * @return string
     */
    public function getPaymentMethod();

    /**
     * Set Payment Method
     * @param string $method
     * @return $this
     */
    public function setPaymentMethod($method);

    /**
     * Get Multi Store Flag
     * @return boolean
     */
    public function getMultiStore();

    /**
     * Get Store Id
     * @param  null|int $marketplaceId
     * @return int
     */
    public function getStore($marketplaceId = null);

    /**
     * Get Marketplace to Store Mapping as Array
     * @return mixed
     */
    public function getMultiStoreValues();

    /**
     * Get Customer Group Id
     * @param null|int $marketplaceId
     * @return int|null
     */
    public function getCustomerGroup($marketplaceId = null);
}
