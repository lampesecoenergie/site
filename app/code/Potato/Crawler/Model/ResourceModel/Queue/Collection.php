<?php

namespace Potato\Crawler\Model\ResourceModel\Queue;

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
            Model\Queue::class,
            Model\ResourceModel\Queue::class
        );
    }

    public function joinPopularity()
    {
        $this->getSelect()
            ->joinLeft(
                array(
                    'popularity' => $this->getTable('po_crawler_popularity')
                ),
                'main_table.url = popularity.url',
                array('view' => 'popularity.view')
            )
            ->order(array('popularity.view DESC','main_table.priority ASC'));
        ;
        return $this;
    }
}