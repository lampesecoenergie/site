<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 19/2/18
 * Time: 12:29 PM
 */

namespace Ced\Cdiscount\Model\ResourceModel;


class Sizes extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cdiscount_sizes','id');
    }
}