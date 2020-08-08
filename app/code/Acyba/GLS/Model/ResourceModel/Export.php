<?php

namespace Acyba\GLS\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Export extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('sales_order_grid', 'entity_id');
    }
}