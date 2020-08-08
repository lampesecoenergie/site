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

namespace Ced\Amazon\Model\Order\Import;

use Ced\Amazon\Api\Data\Order\Import\ParamsInterface;

use Ced\Amazon\Api\Service\ConfigServiceInterface;
use Magento\Framework\DataObject;

class Params extends DataObject implements ParamsInterface
{
    /** @var ConfigServiceInterface  */
    public $config;

    public function __construct(
        ConfigServiceInterface $config,
        array $data = []
    ) {
        parent::__construct($data);
        $this->config = $config;
    }

    /**
     * Set Account Ids
     * @param $value
     * @return $this
     */
    public function setAccountIds($value)
    {
        return $this->setData(self::COLUMN_ACCOUNT_ID_LIST, $value);
    }

    /**
     * Get Account Ids
     * @return mixed
     */
    public function getAccountIds()
    {
        $ids = $this->getData(self::COLUMN_ACCOUNT_ID_LIST);
        if (!is_array($ids)) {
            $ids = [];
        }

        return $ids;
    }

    /**
     * Set Amazon Order Id
     * @param mixed $value
     * @return $this
     */
    public function setAmazonOrderId($value)
    {
        return $this->setData(self::COLUMN_AMAZON_ORDER_ID, $value);
    }

    /**
     * Get Amazon Order Id
     * @return mixed
     */
    public function getAmazonOrderId()
    {
        return $this->getData(self::COLUMN_AMAZON_ORDER_ID);
    }

    /**
     * Set Amazon Buyer Email
     * @param $value
     * @return $this
     */
    public function setBuyerEmail($value)
    {
        return $this->setData(self::COLUMN_AMAZON_BUYER_EMAIL, $value);
    }

    /**
     * Get Amazon Buyer Email
     * @return string
     */
    public function getBuyerEmail()
    {
        return $this->getData(self::COLUMN_AMAZON_BUYER_EMAIL);
    }

    /**
     * Set Amazon Order Status
     * @param string $value
     * @return $this
     */
    public function setStatus($value)
    {
        return $this->setData(self::COLUMN_AMAZON_ORDER_STATUS_LIST, $value);
    }

    /**
     * Get Amazon Order Status
     * @return string
     */
    public function getStatus()
    {
        $status = $this->getData(self::COLUMN_AMAZON_ORDER_STATUS_LIST);
        if (!isset($status) || empty($status) || in_array($status, [
                    \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_UNSHIPPED,
                    \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_PARTIALLY_SHIPPED
                ])
        ) {
            $status = [
                \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_UNSHIPPED,
                \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_PARTIALLY_SHIPPED
            ];
        }

        return $status;
    }

    /**
     * Set Amazon Order Lower Date
     * @param string $value
     * @return $this
     */
    public function setLowerDate($value)
    {
        return $this->setData(self::COLUMN_AMAZON_ORDER_LOWER_DATE, $value);
    }

    /**
     * Get Amazon Order Lower Date
     * @return string
     */
    public function getLowerDate()
    {
        $lower = $this->getData(self::COLUMN_AMAZON_ORDER_LOWER_DATE);
        if (!isset($lower) || empty($lower)) {
            $lower = date('Y-m-d H:i:s O', strtotime($this->config->getImportTime()));
        }
        return  $lower;
    }

    /**
     * Set Amazon Order Upper Date
     * @param string $value
     * @return $this
     */
    public function setUpperDate($value)
    {
        return $this->setData(self::COLUMN_AMAZON_ORDER_UPPER_DATE, $value);
    }

    /**
     * Get Amazon Order Upper Date
     * @return string
     */
    public function getUpperDate()
    {
        return $this->getData(self::COLUMN_AMAZON_ORDER_UPPER_DATE);
    }

    /**
     * Set API Limit
     * @param integer $value
     * @return $this
     */
    public function setLimit($value)
    {
        return $this->setData(self::COLUMN_AMAZON_ORDER_API_LIMIT, $value);
    }

    /**
     * Get API Limit
     * @return integer
     */
    public function getLimit()
    {
        $limit = $this->getData(self::COLUMN_AMAZON_ORDER_API_LIMIT);
        if (empty($limit)) {
            $limit = 100;
        }

        return $limit;
    }

    /**
     * Set Allow Pages
     * @param boolean $value
     * @return $this
     */
    public function setAllowPages($value)
    {
        return $this->setData(self::COLUMN_AMAZON_ALLOW_PAGES, $value);
    }

    /**
     * Get API Limit
     * @return boolean
     */
    public function getAllowPages()
    {
        $pages = $this->getData(self::COLUMN_AMAZON_ALLOW_PAGES);
        if ($pages === false) {
            $pages = true;
        }
        return $pages;
    }

    /**
     * Set Date Type
     * @param string $value
     * @return $this
     */
    public function setType($value)
    {
        return $this->setData(self::COLUMN_DATE_TYPE, $value);
    }

    /**
     * Get Date Type
     * @return boolean
     */
    public function getType()
    {
        $type = $this->getData(self::COLUMN_DATE_TYPE);
        if (empty($type)) {
            $type = "Created";
        }
        return $type;
    }

    /**
     * Set Import Mode
     * @param string $value
     * @return $this
     */
    public function setMode($value)
    {
        return $this->setData(self::COLUMN_IMPORT_MODE, $value);
    }

    /**
     * Get Import Mode
     * @return boolean
     */
    public function getMode()
    {
        $mode = $this->getData(self::COLUMN_IMPORT_MODE);
        if (empty($mode)) {
            $mode = self::IMPORT_MODE_API;
        }
        return $mode;
    }

    /**
     * Set Report Path
     * @param string $value
     * @return $this
     */
    public function setPath($value)
    {
        return $this->setData(self::COLUMN_REPORT_PATH, $value);
    }

    /**
     * Get Report Path
     * @return string
     */
    public function getPath()
    {
        return $this->getData(self::COLUMN_REPORT_PATH);
    }

    /**
     * Set Allow Create Order In Magento
     * @param boolean $value
     * @return $this
     */
    public function setCreate($value)
    {
        return $this->setData(self::COLUMN_CREATE, $value);
    }

    /**
     * Get Allow Create Order In Magento
     * @return boolean
     */
    public function getCreate()
    {
        $flag = $this->getData(self::COLUMN_CREATE);
        return (boolean)$flag;
    }

    /**
     * Set CLI Limit
     * @param string $value
     * @return $this
     */
    public function setCliLimit($value)
    {
        return $this->setData(self::COLUMN_CLI_LIMIT, $value);
    }

    /**
     * Get CLI Limit
     * @return string
     */
    public function getCliLimit()
    {
        $value = $this->getData(self::COLUMN_CLI_LIMIT);
        return $value;
    }

    /**
     * Set Sync Mode
     * @param string $value
     * @return $this
     */
    public function setSyncMode($value)
    {
        return $this->setData(self::COLUMN_SYNC_MODE, $value);
    }

    /**
     * Get Sync Mode
     * @return string
     */
    public function getSyncMode()
    {
        $value = $this->getData(self::COLUMN_SYNC_MODE);
        if (empty($value)) {
            $value = self::COLUMN_SYNC_MODE_FETCH;
        }
        return $value;
    }
}
