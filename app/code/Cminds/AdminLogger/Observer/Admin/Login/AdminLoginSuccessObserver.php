<?php

namespace Cminds\AdminLogger\Observer\Admin\Login;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Manager;

/**
 * Class AdminLoginSuccessObserver
 *
 * @package Cminds\AdminLogger\Observer\Admin\Login
 */
class AdminLoginSuccessObserver implements ObserverInterface
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * AdminLoginSuccessObserver constructor.
     *
     * @param Manager      $manager
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        Manager $manager,
        ModuleConfig $moduleConfig
    ) {
        $this->manager = $manager;
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

        $this->manager->dispatch(
            'cminds_adminlogger_new_action',
            [
                'user' => $event->getData('user'),
                'entity_type' => 'user',
                'action_type' => ModuleConfig::ACTION_ADMIN_LOGIN_SUCCESS
            ]
        );
    }
}
