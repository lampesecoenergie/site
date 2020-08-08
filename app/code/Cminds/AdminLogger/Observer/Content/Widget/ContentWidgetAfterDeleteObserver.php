<?php

namespace Cminds\AdminLogger\Observer\Content\Widget;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Manager;

/**
 * Class ContentWidgetAfterDeleteObserver
 *
 * @package Cminds\AdminLogger\Observer\Content\Widget
 */
class ContentWidgetAfterDeleteObserver implements ObserverInterface
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
     * ContentWidgetAfterDeleteObserver constructor.
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

        $cmsWidget = $observer->getEvent()->getData('object');

        if ($cmsWidget->isDeleted()) {
            $this->eventManager->dispatch(
                'cminds_adminlogger_new_action',
                [
                    'cms_widget' => $cmsWidget,
                    'entity_type' => 'cms_widget',
                    'action_type' => ModuleConfig::ACTION_CONTENT_WIDGET_DELETE
                ]
            );
        }
    }
}
