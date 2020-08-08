<?php

namespace Cminds\AdminLogger\Observer\Order\Invoice;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Manager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

/**
 * Class SalesInvoiceAfterSaveObserver
 *
 * @package Cminds\AdminLogger\Observer\Order\Invoice
 */
class SalesInvoiceAfterSaveObserver implements ObserverInterface
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
     * SalesInvoiceAfterSaveObserver constructor.
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

        $isInvoiceNew = $this->registry->registry('cminds_adminlogger_is_invoice_new');

        if ($isInvoiceNew === null || $isInvoiceNew === false) {
            return;
        }

        $invoice = $observer->getEvent()->getData('invoice_item');
        $this->eventManager->dispatch(
            'cminds_adminlogger_new_action',
            [
                'invoice' => $invoice,
                'entity_type' => 'invoice',
                'action_type' => ModuleConfig::ACTION_ORDER_INVOICE_CREATE
            ]
        );
    }
}
