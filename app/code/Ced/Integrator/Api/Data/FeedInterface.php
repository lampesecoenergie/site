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
 * Interface FeedInterface
 * @package Ced\Integrator\Api\Data
 * @api
 */
interface FeedInterface extends \Ced\Integrator\Api\Data\DataInterface
{
    /**
     * Get feed account
     * @return int
     */
    public function getAccountId();

    /**
     * Set feed account
     * @param int $accountId
     * @return $this
     */
    public function setAccountId($accountId);

    /**
     * Get Feed Id
     * @return string
     */
    public function getFeedId();

    /**
     * Set feed Id
     * @param string $feedId
     * @return $this
     */
    public function setFeedId($feedId);

    /**
     * Get Response File Path
     * @return string
     */
    public function getResponseFile();

    /**
     * Set Response File Path
     * @param string $path
     * @return $this
     */
    public function setResponseFile($path);

    /**
     * Get Feed File Path
     * @return string
     */
    public function getFeedFile();

    /**
     * Set Feed File Path
     * @param string $path
     * @return $this
     */
    public function setFeedFile($path);

    /**
     * Get Type
     * @return string
     */
    public function getType();

    /**
     * Set Type
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * Set Status
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get Status
     * @return string
     */
    public function getStatus();
}
