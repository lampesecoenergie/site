<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model;

use Ced\Integrator\Api\Data\ReportInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Report
 * @package Ced\Amazon\Model
 */
class Report extends AbstractModel implements \Ced\Amazon\Api\Data\ReportInterface
{
    const NAME = 'ced_amazon_report';
    const COLUMN_ID = 'id';
    const COLUMN_REPORT_ID = 'report_id';
    const COLUMN_REQUEST_ID = 'request_id';
    const COLUMN_ACCOUNT_ID = 'account_id';
    const COLUMN_TYPE = 'type';
    const COLUMN_STATUS = 'status';
    const COLUMN_REPORT_FILE = 'report_file';
    const COLUMN_SPECIFICS = 'specifics';
    const COLUMN_CREATED_AT = 'created_at';
    const COLUMN_EXECUTED_AT = 'executed_at';

    public function _construct()
    {
        $this->_init(\Ced\Amazon\Model\ResourceModel\Report::class);
    }

    /**
     * Get report account
     * @return int
     */
    public function getAccountId()
    {
        return $this->getData(self::COLUMN_ACCOUNT_ID);
    }

    /**
     * Set report account
     * @param int $accountId
     * @return $this
     */
    public function setAccountId($accountId)
    {
        return $this->setData(self::COLUMN_ACCOUNT_ID, $accountId);
    }

    /**
     * Get Report Id
     * @return string
     */
    public function getReportId()
    {
        return $this->getData(self::COLUMN_REPORT_ID);
    }

    /**
     * Set report Id
     * @param string $reportId
     * @return $this
     */
    public function setReportId($reportId)
    {
        return $this->setData(self::COLUMN_REPORT_ID, $reportId);
    }

    /**
     * Get Report Id
     * @return string
     */
    public function getRequestId()
    {
        return $this->getData(self::COLUMN_REQUEST_ID);
    }

    /**
     * Set report Id
     * @param string $requestId
     * @return $this
     */
    public function setRequestId($requestId)
    {
        return $this->setData(self::COLUMN_REQUEST_ID, $requestId);
    }

    /**
     * Get Report File Path
     * @return string
     */
    public function getReportFile()
    {
        return $this->getData(self::COLUMN_REPORT_FILE);
    }

    /**
     * Set Report File Path
     * @param string $path
     * @return $this
     */
    public function setReportFile($path)
    {
        return $this->setData(self::COLUMN_REPORT_FILE, $path);
    }

    /**
     * Get Type
     * @return string
     */
    public function getType()
    {
        return $this->getData(self::COLUMN_TYPE);
    }

    /**
     * Set Type
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        return $this->setData(self::COLUMN_TYPE, $type);
    }

    /**
     * Set Status
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(self::COLUMN_STATUS, $status);
    }

    /**
     * Get Status
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::COLUMN_STATUS);
    }

    /**
     * Set Created At
     * @param string $date
     * @return $this
     */
    public function setCreatedAt($date)
    {
        return $this->setData(self::COLUMN_CREATED_AT, $date);
    }

    /**
     * Get Created At
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::COLUMN_CREATED_AT);
    }

    /**
     * Set Executed At
     * @param string $date
     * @return $this
     */
    public function setExecutedAt($date)
    {
        return $this->setData(self::COLUMN_EXECUTED_AT, $date);
    }

    /**
     * Get Executed At
     * @return string
     */
    public function getExecutedAt()
    {
        return $this->getData(self::COLUMN_EXECUTED_AT);
    }
}
