<?php

namespace Potato\Crawler\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Potato\Crawler\Api\Data\CounterInterface;

/**
 * Class Counter
 */
class Counter extends AbstractExtensibleObject implements CounterInterface
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
    public function getValue()
    {
        return $this->_get(self::VALUE);
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }
        
    /**
     * @return string
     */
    public function getDate()
    {
        return $this->_get(self::DATE);
    }

    /**
     * @param string $date
     * @return $this
     */
    public function setDate($date)
    {
        return $this->setData(self::DATE, $date);
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