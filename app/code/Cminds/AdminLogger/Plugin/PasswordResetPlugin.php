<?php

namespace Cminds\AdminLogger\Plugin;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Manager;

/**
 * Class PasswordResetPlugin
 *
 * @package Cminds\AdminLogger\Plugin
 */
class PasswordResetPlugin
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
     * PasswordResetPlugin constructor.
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

    /** Catch all password change request for admin users.
     *
     * @param \Magento\User\Model\User $user
     * @param $result
     *
     * @return mixed
     */
    public function afterSendPasswordResetConfirmationEmail(\Magento\User\Model\User $user, $result)
    {
        if ($this->moduleConfig->isActive() === false) {
            return $result;
        }

        if ($user->getEventPrefix() === 'admin_user') {
            $this->manager->dispatch(
                'cminds_adminlogger_new_action',
                [
                    'user' => $user,
                    'entity_type' => 'user',
                    'reference_value' => $user->getId(),
                    'action_type' => ModuleConfig::ACTION_ADMIN_PASSWORD_CHANGE_REQUEST
                ]
            );
        }

        return $result;
    }
}
