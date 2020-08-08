<?php

namespace Cminds\AdminLogger\Observer\Config;

use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Manager;
use Magento\Config\Model\Config;
use Magento\Config\Model\ResourceModel\Config\Data\Collection as ConfigCollection;
use Magento\Framework\Registry;

/**
 * Class ConfigBeforeSaveObserver
 *
 * @package Cminds\AdminLogger\Observer\Config
 */
class ConfigUpdateObserver implements ObserverInterface
{
    /**
     * @var Manager
     */
    private $eventManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    private $configCollection;


    /**
     * ConfigBeforeSaveObserver constructor.
     *
     * @param Manager          $eventManager
     * @param Config           $config
     * @param Registry         $registry
     * @param ModuleConfig     $moduleConfig
     * @internal param Manager $manager
     */
    public function __construct(
        Manager $eventManager,
        Config $config,
        Registry $registry,
        ModuleConfig $moduleConfig,
        ConfigCollection $configCollection
    ) {
        $this->eventManager = $eventManager;
        $this->config = $config;
        $this->registry = $registry;
        $this->moduleConfig = $moduleConfig;
        $this->configCollection = $configCollection;
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

        $oldConfig = $this->registry->registry('cminds_adminlogger_old_config_data');
        $oldConfigFlatArray = $oldConfig['old_config_array'];
        $oldConfigObject = $oldConfig['old_config_object'];

        $newConfig = $this->configCollection
            ->addScopeFilter(
                $oldConfigObject->getData('scope'),
                $oldConfigObject->getData('scope_id'),
                $oldConfigObject->getData('section'))
            ->getItems();

        $newConfigFlatArray = [];

        foreach ($newConfig as $newConfigItem) {
            $newConfigFlatArray[$newConfigItem->getData('path')] = $newConfigItem->getData('value');
        }

        $configurationUpdate = [
            'old_data' => $oldConfigFlatArray,
            'new_data' => $newConfigFlatArray
        ];

        $this->eventManager->dispatch(
            'cminds_adminlogger_new_action',
            [
                'configuration_update' => $configurationUpdate,
                'reference_value' => 'configuration_update',
                'action_type' => ModuleConfig::ACTION_CONFIGURATION_UPDATE
            ]
        );
    }
}
