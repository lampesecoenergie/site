<?php

namespace Cminds\AdminLogger\Cron;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Cminds\AdminLogger\Model\ResourceModel\AdminLogger\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

/**
 * Class AbstractLogRemover
 *
 * @package Cminds\AdminLogger\Cron
 */
abstract class AbstractLogRemover
{
    /**
     * @var DateTimeFactory
     */
    protected $dateFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;

    /**
     * AbstractLogRemover constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param DateTimeFactory   $dateFactory
     * @param ModuleConfig      $moduleConfig
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        DateTimeFactory $dateFactory,
        ModuleConfig $moduleConfig
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->dateFactory = $dateFactory;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * Remove logs from collection after given days are gone.
     *
     * @param $collection
     * @param $daysAfterCreate
     */
    protected function removeLogs($collection, $daysAfterCreate)
    {
        foreach ($collection->getItems() as $log) {
            $timestampToCompare = $this->dateFactory
                ->create()
                ->timestamp($log->getData('created_at'));

            $timestampToCompare = strtotime(
                '+' . $daysAfterCreate . ' days',
                $timestampToCompare
            );

            $timestampNow = $this->dateFactory
                ->create()
                ->timestamp();

            // check is given period already gone if yes then delete logs
            if ($timestampToCompare > $timestampNow) {
                continue;
            }

            $log->delete();
        }
    }
}
