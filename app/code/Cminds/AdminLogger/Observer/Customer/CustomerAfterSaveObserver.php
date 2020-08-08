<?php

namespace Cminds\AdminLogger\Observer\Customer;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Registry;

/**
 * Class CustomerAfterSaveObserver
 *
 * @package Cminds\AdminLogger\Observer\Customer
 */
class CustomerAfterSaveObserver implements ObserverInterface
{
    /**
     * @var Manager
     */
    private $eventManager;

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
     * CustomerAfterSaveObserver constructor.
     *
     * @param Manager      $manager
     * @param Registry     $registry
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        Manager $manager,
        Registry $registry,
        ModuleConfig $moduleConfig
    ) {
        $this->eventManager = $manager;
        $this->registry = $registry;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * Observer execute method.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->moduleConfig->isActive() === false) {
            return;
        }

        if (self::$triggered === false) {
            $customerAfterSave = $observer->getEvent()->getData('customer');
            $customerBeforeSave = $this->registry->registry('cminds_adminlogger_customer_before_save');
            $isObjectNew = $this->registry->registry('cminds_adminlogger_customer_is_object_new');

            if ($isObjectNew) {
                $this->eventManager->dispatch(
                    'cminds_adminlogger_new_action',
                    [
                        'customer' => $customerAfterSave,
                        'entity_type' => 'customer',
                        'action_type' => ModuleConfig::ACTION_CUSTOMER_CREATE
                    ]
                );
            } elseif ($isObjectNew === false) {
                $customerOldData = $customerBeforeSave;
                $customer = $customerAfterSave;
                $this->eventManager->dispatch(
                    'cminds_adminlogger_new_action',
                    [
                        'customer' => $customer,
                        'customer_old_data' => $customerOldData,
                        'entity_type' => 'customer',
                        'action_type' => ModuleConfig::ACTION_CUSTOMER_UPDATE
                    ]
                );
            }
        }

        self::$triggered = true;
    }
}
