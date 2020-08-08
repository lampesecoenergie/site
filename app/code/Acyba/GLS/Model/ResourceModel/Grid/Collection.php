<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Acyba\GLS\Model\ResourceModel\Grid;

use \Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use \Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use \Magento\Framework\Event\ManagerInterface as EventManager;
use \Psr\Log\LoggerInterface as Logger;

/**
 * Order grid collection
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'sales_order_grid',
        $resourceModel = '\Magento\Sales\Model\ResourceModel\Order'
    ){
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    protected function _initSelect()
    {
        $this->addFilterToMap('entity_id', 'main_table.entity_id');

        parent::_initSelect();

        // only show orders with gls shipping_method
        $this->addFieldToFilter('shipping_information', ['like' => '%GLS%']);
    }
}
