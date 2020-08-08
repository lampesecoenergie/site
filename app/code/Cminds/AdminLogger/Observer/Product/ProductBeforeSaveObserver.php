<?php

namespace Cminds\AdminLogger\Observer\Product;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Registry;

/**
 * Class ProductBeforeSaveObserver
 *
 * @package Cminds\AdminLogger\Observer\Product
 */
class ProductBeforeSaveObserver implements ObserverInterface
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
     * @var Registry
     */
    private $registry;

    /**
     * ProductBeforeSaveObserver constructor.
     *
     * @param Manager $manager
     * @param ModuleConfig $moduleConfig
     * @param Registry $registry
     */
    public function __construct(
        Manager $manager,
        ModuleConfig $moduleConfig,
        Registry $registry
    ) {
        $this->eventManager = $manager;
        $this->moduleConfig = $moduleConfig;
        $this->registry = $registry;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->moduleConfig->isActive() === false) {
            return;
        }

        $product = $observer->getEvent()->getData('product');

        $oldExtensionAttributes = $product->getOrigData()['extension_attributes']->getStockItem()->getData();
        $oldMediaGallery = $product->getOrigData()['media_gallery'];

        $this->registry->register('cminds_campaignmanager_product_old_stock_item', $oldExtensionAttributes);
        $this->registry->register('cminds_campaignmanager_product_old_media_gallery', $oldMediaGallery);
    }
}
