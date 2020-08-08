<?php

namespace Cminds\AdminLogger\Observer\Content\Page;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Manager;

/**
 * Class ContentPageAfterDeleteObserver
 *
 * @package Cminds\AdminLogger\Observer\Content\Page
 */
class ContentPageAfterDeleteObserver implements ObserverInterface
{
    /**
     * @var Manager
     */
    private $eventManager;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * ContentPageAfterDeleteObserver constructor.
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
     */
    public function execute(Observer $observer)
    {
        if ($this->moduleConfig->isActive() === false) {
            return;
        }

        $cmsPage = $observer->getEvent()->getData('object');

        if ($cmsPage->isDeleted()) {
            $this->eventManager->dispatch(
                'cminds_adminlogger_new_action',
                [
                    'cms_page' => $cmsPage,
                    'entity_type' => 'cms_page',
                    'action_type' => ModuleConfig::ACTION_CONTENT_PAGE_DELETE
                ]
            );
        }
    }
}
