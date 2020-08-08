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
 * @package     Ced_Integrator
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Api\Data;

/**
 * Interface QueueInterface
 * @package Ced\Integrator\Api\Data
 * @api
 */
interface QueueInterface extends \Ced\Integrator\Api\Data\DataInterface
{
    /**
     * Get Status of item
     * @return string
     */
    public function getStatus();

    /**
     * Get Type of item
     * @return string
     */
    public function getType();

    /**
     * Get OperationType of item
     * @return string
     */
    public function getOperationType();

    /**
     * Get Marketplace of item
     * @return string
     */
    public function getMarketplace();

    /**
     * Get AccountId of item
     * @return string
     */
    public function getAccountId();

    /**
     * Get Specifics
     * @return array
     */
    public function getSpecifics();

    /**
     * Set Status
     * @param $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Set Executed at
     * @param string $date
     * @return $this
     */
    public function setExecutedAt($date);
}
