<?php

namespace Cminds\AdminLogger\Observer\Content\Block;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

/**
 * Class ContentBlockBeforeSaveObserver
 *
 * @package Cminds\AdminLogger\Observer\Content\Block
 */
class ContentBlockBeforeSaveObserver implements ObserverInterface
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
     * ContentBlockBeforeSaveObserver constructor.
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
        $cmsBlock = $event->getData('object');
        $this->registry->register(
            'cminds_adminlogger_cms_block_is_object_new',
            $cmsBlock->isObjectNew()
        );
    }
}
