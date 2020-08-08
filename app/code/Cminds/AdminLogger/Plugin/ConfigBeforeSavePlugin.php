<?php

namespace Cminds\AdminLogger\Plugin;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Registry;

/**
 * Class ConfigBeforeSavePlugin
 *
 * @package Cminds\AdminLogger\Plugin
 */
class ConfigBeforeSavePlugin
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * ConfigBeforeSavePlugin constructor.
     *
     * @param Registry     $registry
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        Registry $registry,
        ModuleConfig $moduleConfig
    ) {
        $this->registry = $registry;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * Catch config values before save.
     *
     * @param \Magento\Config\Model\Config $config
     */
    public function beforeSave(\Magento\Config\Model\Config $config)
    {
        if ($this->moduleConfig->isActive() === false) {
            return;
        }

        $oldConfigArray = $config->load();

        $oldConfig = [
            'old_config_object' => $config,
            'old_config_array' => $oldConfigArray
        ];

        $this->registry->register('cminds_adminlogger_old_config_data', $oldConfig);
    }
}
