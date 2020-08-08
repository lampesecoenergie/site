<?php

namespace Cminds\AdminLogger\Observer\Order\Invoice;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

/**
 * Class SalesInvoiceBeforeSaveObserver
 *
 * @package Cminds\AdminLogger\Observer\Order\Invoice
 */
class SalesInvoiceBeforeSaveObserver implements ObserverInterface
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
     * SalesInvoiceBeforeSaveObserver constructor.
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

        $invoice = $observer->getEvent()->getData('invoice_item');

        $this->registry->register(
            'cminds_adminlogger_is_invoice_new',
            $invoice->isObjectNew()
        );
    }
}
