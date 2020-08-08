<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Cdiscount\Plugin;

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
     * @var \Ced\Cdiscount\Helper\Config
     */
    public $config;

    public $cache;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigManager,
        \Magento\Framework\App\Cache\TypeListInterface $cache,
        \Magento\Config\Model\ResourceModel\Config $config,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Ced\Cdiscount\Helper\Config $cdiscountConfig
    ) {
        $this->scopeConfigManager = $scopeConfigManager;
        $this->scopeConfigResource = $config;
        $this->messageManager = $messageManager;
        $this->directoryList = $directoryList;
        $this->cache = $cache;
        $this->config = $cdiscountConfig;
    }

    public function afterSave(
        \Magento\Config\Model\Config $subject
    ) {
        $configPost = $subject->getData();
        if (isset($configPost['section']) and $configPost['section'] == 'cdiscount_config') {
            $enabled = $this->config->isEnabled();
            if ($enabled) {
                $response = $this->config->validate();
                if ($response) {
                    $this->messageManager->addSuccessMessage('Cdiscount credentials are valid.');
                    $this->scopeConfigResource->saveConfig(
                        'cdiscount_config/cdiscount_setting/valid',
                        '1',
                        'default',
                        0
                    );
                } else {
                    $this->messageManager->addErrorMessage('Cdiscount credentials are invalid.');
                    $this->scopeConfigResource->saveConfig(
                        'cdiscount_config/cdiscount_setting/valid',
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
