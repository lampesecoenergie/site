<?php

namespace Cminds\AdminLogger\Observer\Product;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Manager;

/**
 * Class ProductAfterSaveObserver
 *
 * @package Cminds\AdminLogger\Observer\Product
 */
class ProductAfterSaveObserver implements ObserverInterface
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
     * ProductCreateObserver constructor.
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
     * ProductCreateObserver execute.
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

        $product = $observer->getEvent()->getData('product');

        if ($product->isObjectNew()) {
            $this->eventManager->dispatch(
                'cminds_adminlogger_new_action',
                [
                    'product' => $product,
                    'entity_type' => 'product',
                    'action_name' => ModuleConfig::ACTION_PRODUCT_CREATE
                ]
            );
        }

        if ($product->isObjectNew() === false && $product->hasDataChanges()) {
            $this->eventManager->dispatch(
                'cminds_adminlogger_new_action',
                [
                    'product' => $product,
                    'entity_type' => 'product',
                    'action_type' => ModuleConfig::ACTION_PRODUCT_UPDATE
                ]
            );
        }
    }
}
