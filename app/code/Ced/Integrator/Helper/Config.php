<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Integrator
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Helper;

/**
 * Directory separator shorthand
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}


class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_PATH_ACTIVE = 'ced_integrator/settings/enable';
    const CONFIG_PATH_USER_ALREADY_REGISTER_FLAG = 'ced_integrator/settings/already_registered';
    const CONFIG_PATH_CEDCOMMERCE_WEBSERVICES_API_TOKEN = 'ced_integrator/settings/api_token';
    const CONFIG_PATH_CEDCOMMERCE_WEBSERVICES_APP_PASSWORD = 'ced_integrator/settings/app_password';
    const CONFIG_PATH_CEDCOMMERCE_WEBSERVICES_APP_USER = 'ced_integrator/settings/app_user';
    const CONFIG_PATH_CEDCOMMERCE_WEBSERVICES_APP_USER_EMAIL = 'ced_integrator/settings/shop_email';
    const CONFIG_PATH_GOOGLE_GEOCODE_KEY = 'ced_integrator/settings/geocode_key';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfigManager;

    /**
     * Debug Log Mode
     * @var boolean
     */
    public $debugMode = true;

    /**
     * constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->scopeConfigManager = $context->getScopeConfig();
    }

    /**
     * is Module Enable
     * @return bool
     */
    public function isEnable()
    {
        $isEnable = false;
        $enable = $this->scopeConfigManager->getValue(self::CONFIG_PATH_ACTIVE);
        if ($enable == 'true') {
            $isEnable = true;
        }
        return $isEnable;
    }

    /**
     * Get shopEmail for config
     * @return string
     */
    public function getShopEmail()
    {
        $shopEmail = $this->scopeConfigManager->getValue(self::CONFIG_PATH_CEDCOMMERCE_WEBSERVICES_APP_USER_EMAIL);
        return $shopEmail;
    }

    /**
     * Get userName for config
     * @return string
     */
    public function getUserName()
    {
        $userName = $this->scopeConfigManager->getValue(self::CONFIG_PATH_CEDCOMMERCE_WEBSERVICES_APP_USER);
        return $userName;
    }

    /**
     * Get password for config
     * @return string
     */
    public function getPassword()
    {
        $password = $this->scopeConfigManager->getValue(self::CONFIG_PATH_CEDCOMMERCE_WEBSERVICES_APP_PASSWORD);

        return $password;
    }

    /**
     * Get apiToken for config
     * @return string
     */
    public function getApiToken()
    {
        $apiToken = $this->scopeConfigManager->getValue(self::CONFIG_PATH_CEDCOMMERCE_WEBSERVICES_API_TOKEN);
        return $apiToken;
    }

    /**
     * Check if user already registered
     * @return bool
     */
    public function isAlreadyRegistered()
    {
        $alreadyRegistered = false;
        $registered = $this->scopeConfigManager->getValue(self::CONFIG_PATH_USER_ALREADY_REGISTER_FLAG);
        if ($registered == true) {
            $alreadyRegistered = true;
        }

        return $alreadyRegistered;
    }

    /**
     * @deprecated
     * @return bool
     */
    public function isValid()
    {
        return true;
    }

    public function getGeocodeKey()
    {
        $key = $this->scopeConfigManager->getValue(self::CONFIG_PATH_GOOGLE_GEOCODE_KEY);
        return $key;
    }
}
