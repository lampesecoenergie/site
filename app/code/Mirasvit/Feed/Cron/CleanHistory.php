<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Feed\Cron;

use Mirasvit\Feed\Model\ResourceModel\Feed\History\CollectionFactory as HistoryCollectionFactory;

class CleanHistory
{
    /**
     * @var HistoryCollectionFactory
     */
    protected $historyCollectionFactory;

    /**
     * @param HistoryCollectionFactory $historyCollectionFactory
     */
    public function __construct(
        HistoryCollectionFactory $historyCollectionFactory
    ) {
        $this->historyCollectionFactory = $historyCollectionFactory;
    }

    /**
     * Execute
     * @return void
     */
    public function execute()
    {
        $date = new \Zend_Date();
        $date->subDay(3);

        $collection = $this->historyCollectionFactory->create()
            ->addFieldToFilter('created_at', ['lt' => $date->toString('Y-MM-dd H:mm:s')]);

        foreach ($collection as $item) {
            $item->delete();
        }
    }
}