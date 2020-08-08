<?php

namespace Cminds\AdminLogger\Observer\Content\Widget;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Widget\Model\Widget\Instance as WidgetInstance;

/**
 * Class ContentWidgetBeforeSaveObserver
 *
 * @package Cminds\AdminLogger\Observer\Content\Widget
 */
class ContentWidgetBeforeSaveObserver implements ObserverInterface
{
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var WidgetInstance
     */
    private $widget;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * ContentWidgetBeforeSaveObserver constructor.
     *
     * @param Registry       $registry
     * @param WidgetInstance $widget
     * @param ModuleConfig   $moduleConfig
     */
    public function __construct(
        Registry $registry,
        WidgetInstance $widget,
        ModuleConfig $moduleConfig
    ) {
        $this->registry = $registry;
        $this->widget = $widget;
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
        $cmsWidget = $event->getData('object');

        $this->registry->register(
            'cminds_adminlogger_cms_widget_is_object_new',
            $cmsWidget->isObjectNew()
        );

        if ($cmsWidget->isObjectNew() === false) {
            $this->registry->register(
                'cminds_adminlogger_cms_widget_old_data',
                $cmsWidget
            );
        }
    }
}
