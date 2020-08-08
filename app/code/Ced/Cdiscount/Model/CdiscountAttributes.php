<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 19/2/18
 * Time: 12:27 PM
 */

namespace Ced\Cdiscount\Model;


class CdiscountAttributes extends \Magento\Framework\Model\AbstractModel
{

    public function _construct()
    {
        $this->_init('Ced\Cdiscount\Model\ResourceModel\CdiscountAttributes');
    }
}