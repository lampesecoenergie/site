<?php

namespace Cminds\AdminLogger\Observer\Order\Address;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Manager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SalesAddressAfterSaveObserver
 *
 * @package Cminds\AdminLogger\Observer\Order\Address
 */
class SalesAddressAfterSaveObserver implements ObserverInterface
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
     * SalesAddressAfterSaveObserver constructor.
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

        $eventObject = $observer->getEvent()->getData('address');
        $addressType = $eventObject->getData('address_type');

        if ($eventObject->hasDataChanges() === false
            || $eventObject->hasDataChanges() === null
        ) {
            return;
        }

        if ($addressType === 'billing') {
            $this->eventManager->dispatch(
                'cminds_adminlogger_new_action',
                [
                    'address' => $eventObject,
                    'entity_type' => 'address',
                    'action_type' => ModuleConfig::ACTION_ORDER_BILLING_ADDRESS_UPDATE
                ]
            );
        }

        if ($addressType === 'shipping') {
            $this->eventManager->dispatch(
                'cminds_adminlogger_new_action',
                [
                    'address' => $eventObject,
                    'entity_type' => 'address',
                    'action_type' => ModuleConfig::ACTION_ORDER_SHIPPING_ADDRESS_UPDATE
                ]
            );
        }
    }
}
