<?php

namespace Potato\Crawler\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Potato\Crawler\Api\Data\PopularityInterface;

/**
 * Class Popularity
 */
class Popularity extends AbstractExtensibleObject implements PopularityInterface
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
    public function getView()
    {
        return $this->_get(self::VIEW);
    }

    /**
     * @param int $view
     * @return $this
     */
    public function setView($view)
    {
        return $this->setData(self::VIEW, $view);
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