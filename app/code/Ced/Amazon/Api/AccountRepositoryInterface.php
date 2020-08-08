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

namespace Ced\Amazon\Api;

/**
 * Interface AccountRepositoryInterface
 * @package Ced\Amazon\Api
 * @api
 */
interface AccountRepositoryInterface extends \Ced\Integrator\Api\AccountRepositoryInterface
{
    /**
     * Get Account By Id
     * @param string $id
     * @return \Ced\Amazon\Api\Data\AccountInterface
     */
    public function getById($id);

    /**
     * Get all active accounts, Active means enabled and status is valid.
     * @param array $ids, can be filtered by only the given ids
     * @return \Ced\Amazon\Api\Data\AccountSearchResultsInterface
     */
    public function getAvailableList($ids = []);
}
