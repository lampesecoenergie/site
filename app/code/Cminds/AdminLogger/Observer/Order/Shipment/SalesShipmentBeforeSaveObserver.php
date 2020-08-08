<?php

namespace Cminds\AdminLogger\Observer\Order\Shipment;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

/**
 * Class SalesShipmentBeforeSaveObserver
 *
 * @package Cminds\AdminLogger\Observer\Order\Shipment
 */
class SalesShipmentBeforeSaveObserver implements ObserverInterface
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
     * @var bool
     */
    private static $triggered = false;

    /**
     * SalesShipmentBeforeSaveObserver constructor.
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

        $shipment = $observer->getEvent()->getData('shipment_item');

        if (self::$triggered === true) {
            return;
        }

        $this->registry->register(
            'cminds_adminlogger_is_shipment_new',
            $shipment->isObjectNew()
        );

        self::$triggered = true;
    }
}
