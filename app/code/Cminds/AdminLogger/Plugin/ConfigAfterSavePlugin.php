<?php

namespace Cminds\AdminLogger\Plugin;

use Magento\Framework\Event\Manager;
use Cminds\AdminLogger\Model\Config as ModuleConfig;

/**
 * Class ConfigAfterSavePlugin
 *
 * @package Cminds\AdminLogger\Plugin
 */
class ConfigAfterSavePlugin
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
     * ConfigAfterSavePlugin constructor.
     *
     * @param Manager      $eventManager
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        Manager $eventManager,
        ModuleConfig $moduleConfig
    ) {
        $this->eventManager = $eventManager;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * @param \Magento\Config\Model\Config $config
     * @param $result
     *
     * @return object
     */
    public function afterSave(\Magento\Config\Model\Config $config, $result)
    {
        if ($this->moduleConfig->isActive() === false) {
            return $result;
        }

        $this->eventManager->dispatch('cminds_adminlogger_config_update', ['new_config' => $config]);

        return $result;
    }
}
