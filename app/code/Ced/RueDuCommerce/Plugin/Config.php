<?php

namespace Ced\RueDuCommerce\Plugin;

/**
 * Directory separator shorthand
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

class Config
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfigManager;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    public $scopeConfigResource;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    public $directoryList;

    /**
     * @var \Ced\RueDuCommerce\Helper\Config
     */
    public $config;

    public $cache;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigManager,
        \Magento\Framework\App\Cache\TypeListInterface $cache,
        \Magento\Config\Model\ResourceModel\Config $config,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Ced\RueDuCommerce\Helper\Config $rueducommerceConfig
    ) {
        $this->scopeConfigManager = $scopeConfigManager;
        $this->scopeConfigResource = $config;
        $this->messageManager = $messageManager;
        $this->directoryList = $directoryList;
        $this->cache = $cache;
        $this->config = $rueducommerceConfig;
    }

    public function afterSave(
        \Magento\Config\Model\Config $subject
    ) {
        $configPost = $subject->getData();
        if (isset($configPost['section']) and $configPost['section'] == 'rueducommerce_config') {
            $enabled = $this->config->isEnabled();
            if ($enabled) {
                $response = $this->config->validate();
                if ($response) {
                    $this->messageManager->addSuccessMessage('RueDuCommerce credentials are valid.');
                    $this->scopeConfigResource->saveConfig(
                        'rueducommerce_config/rueducommerce_setting/valid',
                        '1',
                        'default',
                        0
                    );
                } else {
                    $this->messageManager->addErrorMessage('RueDuCommerce credentials are invalid.');
                    $this->scopeConfigResource->saveConfig(
                        'rueducommerce_config/rueducommerce_setting/valid',
                        '0',
                        'default',
                        0
                    );
                }

                // Cleaning cache
                $cacheType = [
                    'config',
                ];
                foreach ($cacheType as $cache) {
                    $this->cache->cleanType($cache);
                }
            }
        }
    }
}
