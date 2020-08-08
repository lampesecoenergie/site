<?php

namespace Cminds\AdminLogger\Observer\Category;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Manager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class CategoryAfterDeleteObserver
 *
 * @package Cminds\AdminLogger\Observer\Category
 */
class CategoryAfterDeleteObserver implements ObserverInterface
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
     * CategoryDeleteObserver constructor.
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
     * CategoryDeleteObserver execute.
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

        if ($category->isDeleteable()) {
            $this->eventManager->dispatch(
                'cminds_adminlogger_new_action',
                [
                    'category' => $category,
                    'entity_type' => 'category',
                    'action_type' => ModuleConfig::ACTION_CATEGORY_DELETE,
                ]
            );
        }
    }
}
