<?php

namespace Potato\Crawler\Api\Data;

use Magento\Framework\Api\CustomAttributesDataInterface;

/**
 * @api
 */
interface QueueInterface extends CustomAttributesDataInterface
{
    const ID = 'id';
    const STORE_ID = 'store_id';
    const URL = 'url';
    const CUSTOMER_GROUP_ID = 'customer_group_id';
    const USERAGENT = 'useragent';
    const CURRENCY = 'currency';
    const PRIORITY = 'priority';
    
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);
        
    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);
        
    /**
     * @return string
     */
    public function getUrl();

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url);
        
    /**
     * @return int
     */
    public function getCustomerGroupId();

    /**
     * @param int $customerGroupId
     * @return $this
     */
    public function setCustomerGroupId($customerGroupId);
        
    /**
     * @return string
     */
    public function getUseragent();

    /**
     * @param string $useragent
     * @return $this
     */
    public function setUseragent($useragent);
        
    /**
     * @return string
     */
    public function getCurrency();

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency);
        
    /**
     * @return int
     */
    public function getPriority();

    /**
     * @param int $priority
     * @return $this
     */
    public function setPriority($priority);
}