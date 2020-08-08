<?php

namespace Cminds\AdminLogger\Observer\Order\Status;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Manager;

/**
 * Class OrderItemAfterSaveObserver
 *
 * @package Cminds\AdminLogger\Observer\Order\Status
 */
class OrderItemAfterSaveObserver implements ObserverInterface
{
    /**
     * @var Manager
     */
    private $eventManager;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * OrderItemAfterSaveObserver constructor.
     *
     * @param Manager      $eventManager
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        Manager $eventManager,
        ModuleConfig $moduleConfig
    ) {
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

        $eventObject = $observer->getData('order');

        if ($eventObject->hasDataChanges() === false || $eventObject->hasDataChanges() === null) {
            return;
        }

        $status =[];
        $status['new_value'] = $eventObject->getData('status');
        $status['old_value'] = $eventObject->getOrigData('status');

        if ($status['old_value'] === $status['new_value']) {
            return;
        }

        $this->eventManager->dispatch(
            'cminds_adminlogger_new_action',
            [
                'order' => $eventObject,
                'status' => $status,
                'entity_type' => 'order',
                'action_type' => ModuleConfig::ACTION_ORDER_STATUS_UPDATE
            ]
        );
    }
}
