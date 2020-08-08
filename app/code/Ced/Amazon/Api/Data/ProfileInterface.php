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
 * Interface ProfileInterface
 * @package Ced\Amazon\Api\Data
 * @api
 */
interface ProfileInterface extends \Ced\Integrator\Api\Data\ProfileInterface
{
    /**
     * Get profile store id
     * @return int
     */
    public function getStoreId();

    /**
     * Get associated account with profile
     * @return \Ced\Amazon\Api\Data\AccountInterface
     */
    public function getAccountId();

    /**
     * Get profile's comma separated marketplace ids
     * @return string
     */
    public function getMarketplace();

    /**
     * Get profile's marketplace ids as an array
     * @return array
     */
    public function getMarketplaceIds();

    /**
     * Set Status
     * @param int $status
     * @return $this
     */
    public function setProfileSatus($status);

    /**
     * Get Profile Store
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore();

    /**
     * Get Profile Category
     * @return string
     */
    public function getProfileCategory();

    /**
     * Get Profile Sub Category
     * @return string
     */
    public function getProfileSubCategory();

    /**
     * Get Profile Attributes
     * @return array
     */
    public function getProfileAttributes();

    /**
     * Get Barcode Exemption
     * @return bool
     */
    public function getBarcodeExemption();

    /**
     * Set Barcode Exemption
     * @param boolean $value
     * @return $this
     */
    public function setBarcodeExemption($value);
}
