<?php

namespace Potato\Crawler\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Potato\Crawler\Api\Data\QueueInterface;

/**
 * Class Queue
 */
class Queue extends AbstractExtensibleObject implements QueueInterface
{
    /**
     * @return int
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }
        
    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }
        
    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->_get(self::URL);
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        return $this->setData(self::URL, $url);
    }
        
    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->_get(self::CUSTOMER_GROUP_ID);
    }

    /**
     * @param int $customerGroupId
     * @return $this
     */
    public function setCustomerGroupId($customerGroupId)
    {
        return $this->setData(self::CUSTOMER_GROUP_ID, $customerGroupId);
    }
        
    /**
     * @return string
     */
    public function getUseragent()
    {
        return $this->_get(self::USERAGENT);
    }

    /**
     * @param string $useragent
     * @return $this
     */
    public function setUseragent($useragent)
    {
        return $this->setData(self::USERAGENT, $useragent);
    }
        
    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->_get(self::CURRENCY);
    }

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        return $this->setData(self::CURRENCY, $currency);
    }
        
    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->_get(self::PRIORITY);
    }

    /**
     * @param int $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        return $this->setData(self::PRIORITY, $priority);
    }
    
    /**
     * @api
     * @return array
     */
    public function toArray()
    {
        return $this->__toArray();
    }
}