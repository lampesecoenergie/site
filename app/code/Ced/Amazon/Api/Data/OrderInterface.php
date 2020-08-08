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
 * Interface OrderInterface
 * @package Ced\Amazon\Api\Data
 * @api
 */
interface OrderInterface extends \Ced\Integrator\Api\Data\OrderInterface
{
    /**
     * Get Amamzon Order Id
     * @return string
     */
    public function getAmazonOrderId();

    /**
     * Get Magento Increment Id
     * @return string
     */
    public function geMagentoIncrementId();

    /**
     * Get Amamzon Order Place Date
     * @return string
     */
    public function getOrderPlaceDate();

    /**
     * Get Amamzon Order Status
     * @return string
     */
    public function getStatus();

    /**
     * Get Account Id
     * @return int
     */
    public function getAccountId();

    /**
     * Get Amamzon Order Fulfillments
     * @return string
     */
    public function getFulfillments();

    /**
     * Get Amamzon Order Adjustments
     * @return string
     */
    public function getAdjustments();

    /**
     * Get Amamzon Order Data
     * @return string
     */
    public function getOrderData();

    /**
     * Get Amamzon Order Items
     * @return string
     */
    public function getOrderItems();
}
