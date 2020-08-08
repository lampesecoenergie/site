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
 * Interface ReportRepositoryInterface
 * @package Ced\Amazon\Api
 * @api
 */
interface ReportRepositoryInterface extends \Ced\Integrator\Api\ReportRepositoryInterface
{
    /**
     * Request Report from Marketplace
     * @param array $specifics
     * @return boolean|int
     */
    public function request($specifics = []);

    /**
     * Get Report result
     * @param $requestId
     * @param \Ced\Amazon\Api\Data\ReportInterface|null $report
     * @return boolean
     */
    public function get($requestId, $report = null);

    /**
     * @param $id
     * @return \Ced\Amazon\Api\Data\ReportInterface
     */
    public function getById($id);

    /**
     * Get Report By Report Id
     * @param $id
     * @return \Ced\Amazon\Api\Data\ReportInterface
     */
    public function getByReportId($id);

    /**
     * Get Report By Request Id
     * @param $id
     * @return \Ced\Amazon\Api\Data\ReportInterface
     */
    public function getByRequestId($id);

    /**
     * Get all Data
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ced\Amazon\Api\Data\ReportSearchResultsInterface
     * @throws \Exception
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
