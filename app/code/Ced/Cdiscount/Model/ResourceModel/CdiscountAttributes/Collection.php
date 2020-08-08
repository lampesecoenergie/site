<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 19/2/18
 * Time: 12:32 PM
 */

namespace Ced\Cdiscount\Model\ResourceModel\CdiscountAttributes;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    public function _construct()
    {
        $this->_init('Ced\Cdiscount\Model\CdiscountAttributes', 'Ced\Cdiscount\Model\ResourceModel\CdiscountAttributes');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}