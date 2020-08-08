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

namespace Ced\Integrator\Api;

/**
 * Interface RepositoryInterface
 * @package Ced\Integrator\Api
 * @api
 */
interface RepositoryInterface
{
    /**
     * Get a Data by Id
     * @param string $id
     * @return \Ced\Integrator\Api\Data\DataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Get all Data
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ced\Integrator\Api\Data\SearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete a data
     * @param int $id
     * @return bool
     */
    public function delete($id);

    /**
     * Clear cache data for id
     * @param $id
     * @return void
     */
    public function clean($id);

    /**
     * Refresh data in cache
     * @param $id
     * @return void
     * @throws \Exception
     */
    public function refresh($id);
}
