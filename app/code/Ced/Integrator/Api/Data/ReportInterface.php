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
 * Interface ReportInterface
 * @package Ced\Integrator\Api\Data
 * @api
 */
interface ReportInterface extends \Ced\Integrator\Api\Data\DataInterface
{
    /**
     * Get report account
     * @return int
     */
    public function getAccountId();

    /**
     * Set report account
     * @param int $accountId
     * @return $this
     */
    public function setAccountId($accountId);

    /**
     * Get Report Id
     * @return string
     */
    public function getReportId();

    /**
     * Set report Id
     * @param string $reportId
     * @return $this
     */
    public function setReportId($reportId);

    /**
     * Get Report Id
     * @return string
     */
    public function getRequestId();

    /**
     * Set report Id
     * @param string $requestId
     * @return $this
     */
    public function setRequestId($requestId);

    /**
     * Get Report File Path
     * @return string
     */
    public function getReportFile();

    /**
     * Set Report File Path
     * @param string $path
     * @return $this
     */
    public function setReportFile($path);

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

    /**
     * Set Created At
     * @param string $date
     * @return $this
     */
    public function setCreatedAt($date);

    /**
     * Get Created At
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set Executed At
     * @param string $date
     * @return $this
     */
    public function setExecutedAt($date);

    /**
     * Get Executed At
     * @return string
     */
    public function getExecutedAt();
}
