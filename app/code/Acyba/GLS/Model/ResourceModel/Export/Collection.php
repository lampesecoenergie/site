<?php

namespace Acyba\GLS\Model\ResourceModel\Export;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Acyba\GLS\Model\Export',
            'Acyba\GLS\Model\ResourceModel\Export'
        );
    }
}