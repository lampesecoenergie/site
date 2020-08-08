<?php

namespace Cminds\AdminLogger\Observer\Customer;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Registry;

/**
 * Class CustomerBeforeSaveObserver
 *
 * @package Cminds\AdminLogger\Observer\Customer
 */
class CustomerBeforeSaveObserver implements ObserverInterface
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
     * @var Customer
     */
    private $customer;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * CustomerBeforeSaveObserver constructor.
     *
     * @param Manager      $manager
     * @param Registry     $registry
     * @param Customer     $customer
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        Manager $manager,
        Registry $registry,
        Customer $customer,
        ModuleConfig $moduleConfig
    ) {
        $this->eventManager = $manager;
        $this->registry = $registry;
        $this->customer = $customer;
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

        $event = $observer->getEvent();

        // According to duplicated event dispatch we have to check is that the first trigger in request
        if ($this->registry->registry('cminds_adminlogger_customer_before_save') === null
            && $this->registry->registry('cminds_adminlogger_customer_is_object_new') === null
        ) {
            if ($event->getData('customer')->hasDataChanges()
                && $event->getData('customer')->isObjectNew() === false
            ) {
                //load customer data and save it to registry
                $this->registry->register(
                    'cminds_adminlogger_customer_before_save',
                    $this->customer->load(
                        $event->getData('customer')->getId()
                    )
                );
                $this->registry->register(
                    'cminds_adminlogger_customer_is_object_new',
                    $event->getData('customer')->isObjectNew()
                );
            } elseif ($event->getData('customer')->isObjectNew()) {
                $this->registry->register(
                    'cminds_adminlogger_customer_is_object_new',
                    $event->getData('customer')->isObjectNew()
                );
            }
        }
    }
}
