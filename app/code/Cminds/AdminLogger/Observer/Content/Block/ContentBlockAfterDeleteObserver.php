<?php

namespace Cminds\AdminLogger\Observer\Content\Block;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Manager;

/**
 * Class ContentBlockAfterDeleteObserver
 *
 * @package Cminds\AdminLogger\Observer\Content\Block
 */
class ContentBlockAfterDeleteObserver implements ObserverInterface
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
     * ContentBlockAfterDeleteObserver constructor.
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

        $cmsBlock = $observer->getEvent()->getData('object');

        if ($cmsBlock->isDeleted()) {
            $this->eventManager->dispatch(
                'cminds_adminlogger_new_action',
                [
                    'cms_block' => $cmsBlock,
                    'entity_type' => 'cms_block',
                    'action_type' => ModuleConfig::ACTION_CONTENT_BLOCK_DELETE
                ]
            );
        }
    }
}
