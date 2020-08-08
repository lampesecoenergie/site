<?php

namespace Cminds\AdminLogger\Observer\Content\Page;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Registry;

/**
 * Class ContentPageAfterSaveObserver
 *
 * @package Cminds\AdminLogger\Observer\Content\Page
 */
class ContentPageAfterSaveObserver implements ObserverInterface
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
     * ContentPageAfterSaveObserver constructor.
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

        $event = $observer->getEvent();

        $cmsPage = $event->getData('object');
        $isObjectNew = $this->registry->registry('cminds_adminlogger_cms_page_is_object_new');

        if ($isObjectNew) {
            $this->eventManager->dispatch(
                'cminds_adminlogger_new_action',
                [
                    'cms_page' => $cmsPage,
                    'entity_type' => 'cms_page',
                    'action_type' => ModuleConfig::ACTION_CONTENT_PAGE_CREATE
                ]
            );
        } elseif ($isObjectNew === false && $cmsPage->hasDataChanges()) {
            $this->eventManager->dispatch(
                'cminds_adminlogger_new_action',
                [
                    'cms_page' => $cmsPage,
                    'entity_type' => 'cms_page',
                    'action_type' => ModuleConfig::ACTION_CONTENT_PAGE_UPDATE
                ]
            );
        }
    }
}
