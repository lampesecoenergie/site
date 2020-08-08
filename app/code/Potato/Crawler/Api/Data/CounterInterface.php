<?php

namespace Potato\Crawler\Api\Data;

use Magento\Framework\Api\CustomAttributesDataInterface;

/**
 * @api
 */
interface CounterInterface extends CustomAttributesDataInterface
{
    const ID = 'id';
    const VALUE = 'value';
    const DATE = 'date';
    
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
    public function getValue();

    /**
     * @param int $value
     * @return $this
     */
    public function setValue($value);
        
    /**
     * @return string
     */
    public function getDate();

    /**
     * @param string $date
     * @return $this
     */
    public function setDate($date);
}