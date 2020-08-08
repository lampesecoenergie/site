<?php

namespace BoostMyShop\AdminLogger\Model\ResourceModel\Log;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdminLogger\Model\Log', 'BoostMyShop\AdminLogger\Model\ResourceModel\Log');
    }


}