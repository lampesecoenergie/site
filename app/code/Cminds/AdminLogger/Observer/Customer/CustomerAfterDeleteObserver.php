<?php

namespace Cminds\AdminLogger\Observer\Customer;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Manager;

/**
 * Class CustomerAfterDeleteObserver
 *
 * @package Cminds\AdminLogger\Observer\Customer
 */
class CustomerAfterDeleteObserver implements ObserverInterface
{
    /**
     * Event Manager object.
     *
     * @var Manager
     */
    private $eventManager;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * CustomerAfterDeleteObserver constructor.
     *
     * @param Manager      $manager
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        Manager $manager,
        ModuleConfig $moduleConfig
    ) {
        $this->eventManager = $manager;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * Observer execute method.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->moduleConfig->isActive() === false) {
            return;
        }

        $customer = $observer->getEvent()->getData('customer');

        if ($customer->isDeleted()) {
            $this->eventManager->dispatch(
                'cminds_adminlogger_new_action',
                [
                    'customer' => $customer,
                    'entity_type' => 'customer',
                    'action_type' => ModuleConfig::ACTION_CUSTOMER_DELETE
                ]
            );
        }
    }
}
