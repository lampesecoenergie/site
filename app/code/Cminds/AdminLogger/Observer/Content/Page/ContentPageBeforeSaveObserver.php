<?php

namespace Cminds\AdminLogger\Observer\Content\Page;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

/**
 * Class ContentPageBeforeSaveObserver
 *
 * @package Cminds\AdminLogger\Observer\Content\Page
 */
class ContentPageBeforeSaveObserver implements ObserverInterface
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
     * ContentPageBeforeSaveObserver constructor.
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
        $cmsPage = $event->getData('object');
        $this->registry->register(
            'cminds_adminlogger_cms_page_is_object_new',
            $cmsPage->isObjectNew()
        );
    }
}
