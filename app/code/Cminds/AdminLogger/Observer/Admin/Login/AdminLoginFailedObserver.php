<?php

namespace Cminds\AdminLogger\Observer\Admin\Login;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Manager;
use Magento\User\Model\User;

/**
 * Class AdminLoginFailedObserver
 *
 * @package Cminds\AdminLogger\Observer\Admin\Login
 */
class AdminLoginFailedObserver implements ObserverInterface
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var User
     */
    private $user;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * AdminLoginFailedObserver constructor.
     *
     * @param Manager      $manager
     * @param User         $user
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        Manager $manager,
        User $user,
        ModuleConfig $moduleConfig
    ) {
        $this->manager = $manager;
        $this->user = $user;
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
        $user = $this->user->loadByUsername($event->getData('user_name'));

        $this->manager->dispatch(
            'cminds_adminlogger_new_action',
            [
                'user_name' => $event->getData('user_name'),
                'user' => $user,
                'reference_value' => $user->getId(),
                'entity_type' => 'user',
                'action_type' => ModuleConfig::ACTION_ADMIN_LOGIN_FAILED
            ]
        );
    }
}
