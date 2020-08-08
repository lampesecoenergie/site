<?php

namespace Ced\Amazon\Api\Data\Order\Import;

interface ParamsInterface
{
    const COLUMN_ACCOUNT_ID_LIST = 'account_ids';
    const COLUMN_AMAZON_ORDER_ID = 'amazon_order_id';
    const COLUMN_AMAZON_BUYER_EMAIL = 'buyer_email';
    const COLUMN_AMAZON_ORDER_STATUS_LIST = 'status';
    const COLUMN_AMAZON_ORDER_LOWER_DATE = 'lower_date';
    const COLUMN_AMAZON_ORDER_UPPER_DATE = 'upper_date';
    const COLUMN_AMAZON_ORDER_API_LIMIT = 'limit';
    const COLUMN_AMAZON_ALLOW_PAGES = 'allow_pages';
    const COLUMN_DATE_TYPE = 'type';
    const COLUMN_IMPORT_MODE = 'mode';
    const COLUMN_REPORT_PATH = 'path';
    const COLUMN_CREATE = 'create';
    const COLUMN_CLI_LIMIT = 'cli_limit';
    const COLUMN_SYNC_MODE = 'sync_mode';
    const COLUMN_SYNC_MODE_FETCH = 'sync_mode_fetch';
    const COLUMN_SYNC_MODE_NO_FETCH = 'sync_mode_no_fetch';

    const IMPORT_MODE_API = "api";
    const IMPORT_MODE_REPORT = "report";

    /**
     * Set Account Ids
     * @param $value
     * @return $this
     */
    public function setAccountIds($value);

    /**
     * Get Account Ids
     * @return mixed
     */
    public function getAccountIds();

    /**
     * Set Amazon Order Id
     * @param mixed $value
     * @return $this
     */
    public function setAmazonOrderId($value);

    /**
     * Get Amazon Order Id
     * @return mixed
     */
    public function getAmazonOrderId();

    /**
     * Set Amazon Buyer Email
     * @param $value
     * @return $this
     */
    public function setBuyerEmail($value);

    /**
     * Get Amazon Buyer Email
     * @return string
     */
    public function getBuyerEmail();

    /**
     * Set Amazon Order Status
     * @param string $value
     * @return $this
     */
    public function setStatus($value);

    /**
     * Get Amazon Order Status
     * @return string
     */
    public function getStatus();

    /**
     * Set Amazon Order Lower Date
     * @param string $value
     * @return $this
     */
    public function setLowerDate($value);

    /**
     * Get Amazon Order Lower Date
     * @return string
     */
    public function getLowerDate();

    /**
     * Set Amazon Order Upper Date
     * @param string $value
     * @return $this
     */
    public function setUpperDate($value);

    /**
     * Get Amazon Order Upper Date
     * @return string
     */
    public function getUpperDate();

    /**
     * Set API Limit
     * @param integer $value
     * @return $this
     */
    public function setLimit($value);

    /**
     * Get API Limit
     * @return integer
     */
    public function getLimit();

    /**
     * Set Allow Pages
     * @param boolean $value
     * @return $this
     */
    public function setAllowPages($value);

    /**
     * Get API Limit
     * @return boolean
     */
    public function getAllowPages();

    /**
     * Set Allow Create Order In Magento
     * @param boolean $value
     * @return $this
     */
    public function setCreate($value);

    /**
     * Get Allow Create Order In Magento
     * @return boolean
     */
    public function getCreate();

    /**
     * Set Date Type
     * @param string $value
     * @return $this
     */
    public function setType($value);

    /**
     * Get Date Type
     * @return string
     */
    public function getType();

    /**
     * Set Import Mode
     * @param string $value
     * @return $this
     */
    public function setMode($value);

    /**
     * Get Import Mode
     * @return boolean
     */
    public function getMode();

    /**
     * Set Report Path
     * @param string $value
     * @return $this
     */
    public function setPath($value);

    /**
     * Get Report Path
     * @return string
     */
    public function getPath();

    /**
     * Set CLI Limit
     * @param string $value
     * @return $this
     */
    public function setCliLimit($value);

    /**
     * Get CLI Limit
     * @return string
     */
    public function getCliLimit();

    /**
     * Set Sync Mode
     * @param string $value
     * @return $this
     */
    public function setSyncMode($value);

    /**
     * Get Sync Mode
     * @return string
     */
    public function getSyncMode();
}
