<?php

namespace Cminds\AdminLogger\Observer\Order\Shipment;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Manager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

/**
 * Class SalesShipmentAfterSaveObserver
 *
 * @package Cminds\AdminLogger\Observer\Order\Shipment
 */
class SalesShipmentAfterSaveObserver implements ObserverInterface
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
     * SalesShipmentAfterSaveObserver constructor.
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

        $isShipmentNew = $this->registry->registry('cminds_adminlogger_is_shipment_new');

        if ($isShipmentNew === null || $isShipmentNew === false) {
            return;
        }

        $shipment = $observer->getEvent()->getData('shipment_item');
        $this->eventManager->dispatch(
            'cminds_adminlogger_new_action',
            [
                'shipment' => $shipment,
                'entity_type' => 'shipment',
                'action_type' => ModuleConfig::ACTION_ORDER_SHIPMENT_CREATE
            ]
        );
    }
}
