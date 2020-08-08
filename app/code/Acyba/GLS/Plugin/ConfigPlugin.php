<?php

namespace Acyba\GLS\Plugin;

use \Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class ConfigPlugin
 * This class sets the cron settings after back office config is saved
 * @package Acyba\GLS\Plugin
 */
class ConfigPlugin
{
    const CRON_STRING_PATH = 'gls_section/gls_import_export/gls_cron_expression';

    protected $scopeConfig;
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_resourceConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_resourceConfig = $resourceConfig;
    }

    public function afterSave()
    {

        $enabled = $this->scopeConfig->getValue('gls_section/gls_import_export/gls_active');
        $frequncy = $this->scopeConfig->getValue('gls_section/gls_import_export/gls_frequence');

        if ($enabled && $frequncy) {
            $cronExprString = '*/' . $frequncy . ' * * * *';
        } else {
            $cronExprString = '';
        }

        try {
            $this->_resourceConfig->saveConfig(
                self::CRON_STRING_PATH,
                $cronExprString,
                'default',
                0
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to save the cron expression.'));
        }
    }
}