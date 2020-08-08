<?php

namespace Potato\Crawler\Model\ResourceModel\Counter;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Potato\Crawler\Model;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    /**
     * Init collection and determine table names
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            Model\Counter::class,
            Model\ResourceModel\Counter::class
        );
    }
}