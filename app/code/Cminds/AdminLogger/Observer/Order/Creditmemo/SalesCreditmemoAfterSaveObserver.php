<?php

namespace Cminds\AdminLogger\Observer\Order\Creditmemo;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Manager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

/**
 * Class SalesCreditmemoAfterSaveObserver
 *
 * @package Cminds\AdminLogger\Observer\Order\Creditmemo
 */
class SalesCreditmemoAfterSaveObserver implements ObserverInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Manager
     */
    private $eventManager;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * SalesCreditmemoAfterSaveObserver constructor.
     *
     * @param Registry     $registry
     * @param Manager      $eventManager
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        Registry $registry,
        Manager $eventManager,
        ModuleConfig $moduleConfig
    ) {
        $this->registry = $registry;
        $this->eventManager = $eventManager;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->moduleConfig->isActive() === false) {
            return;
        }

        $isCreditmemoNew = $this->registry->registry('cminds_adminlogger_is_creditmemo_new');

        if ($isCreditmemoNew === null || $isCreditmemoNew === false) {
            return;
        }

        $creditmemo = $observer->getEvent()->getData('creditmemo_item');
        $this->eventManager->dispatch(
            'cminds_adminlogger_new_action',
            [
                'credimemo' => $creditmemo,
                'entity_type' => 'credimemo',
                'action_type' => ModuleConfig::ACTION_ORDER_CREDITMEMO_CREATE
            ]
        );
    }
}
