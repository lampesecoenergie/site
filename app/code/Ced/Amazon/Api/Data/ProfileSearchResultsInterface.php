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
 * Interface ProfileSearchResultsInterface
 * @package Ced\Amazon\Api\Data
 * @api
 */
interface ProfileSearchResultsInterface extends \Ced\Integrator\Api\Data\ProfileSearchResultsInterface
{
    /**
     * Accounts for the ids available in the search result
     * @return \Ced\Amazon\Api\Data\AccountSearchResultsInterface
     */
    public function getAccounts();

    /**
     * Get profiles arranged by store ids
     * @return array
     */
    public function getProfileByStoreIdWise();
}
