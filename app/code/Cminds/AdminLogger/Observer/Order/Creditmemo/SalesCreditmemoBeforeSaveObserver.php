<?php

namespace Cminds\AdminLogger\Observer\Order\Creditmemo;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

/**
 * Class SalesCreditmemoBeforeSaveObserver
 *
 * @package Cminds\AdminLogger\Observer\Order\Creditmemo
 */
class SalesCreditmemoBeforeSaveObserver implements ObserverInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * SalesCreditmemoBeforeSaveObserver constructor.
     *
     * @param Registry     $registry
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        Registry $registry,
        ModuleConfig $moduleConfig
    ) {
        $this->registry = $registry;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->moduleConfig->isActive() === false) {
            return;
        }

        $creditmemo = $observer->getEvent()->getData('creditmemo_item');

        $this->registry->register(
            'cminds_adminlogger_is_creditmemo_new',
            $creditmemo->isObjectNew()
        );
    }
}
