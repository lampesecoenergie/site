<?php

namespace Cminds\AdminLogger\Observer\Category;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Manager;

/**
 * Class CategoryAfterSaveObserver
 *
 * @package Cminds\AdminLogger\Observer\Category
 */
class CategoryAfterSaveObserver implements ObserverInterface
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
     * CategoryCreateObserver constructor.
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
     * CategoryCreateObserver execute.
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

        $category = $observer->getEvent()->getData('category');

        if ($category->isObjectNew()) {
            $this->eventManager->dispatch(
                'cminds_adminlogger_new_action',
                [
                    'category' => $category,
                    'entity_type' => 'category',
                    'action_type' => ModuleConfig::ACTION_CATEGORY_CREATE
                ]
            );
        }

        if ($category->isObjectNew() === false && $category->hasDataChanges()) {
            $this->eventManager->dispatch(
                'cminds_adminlogger_new_action',
                [
                    'category' => $category,
                    'entity_type' => 'category',
                    'action_type' => ModuleConfig::ACTION_CATEGORY_UPDATE
                ]
            );
        }
    }
}
