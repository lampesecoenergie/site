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
 * @category  Ced
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Helper;

/**
 * Directory separator shorthand
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

use Magento\Framework\App\Helper\Context;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfigManager;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    public $dl;

    /**
     * @var $userId
     */
    public $userId;

    public $shippingMethod;

    public $shippingPrice;

    public $shippingMethods;

    public $additionalShippingPrice;

    /**
     * @var $apiKey
     */
    public $apiKey;

    /**
     * @var $endpoint
     */
    public $endpoint;

    /**
     * Debug Log Mode
     *
     * @var boolean
     */
    public $debugMode = true;

    public $config;

    public $generator;

    public $logger;

    /**
     * Config constructor.
     * @param Context $context
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Xml\GeneratorFactory $generator
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Xml\GeneratorFactory $generator,
        \Ced\Cdiscount\Helper\Logger $logger
    ) {
        parent::__construct($context);
        $this->scopeConfigManager = $context->getScopeConfig();
        $this->objectManager = $objectManager;
        $this->dl = $directoryList;
        $this->generator = $generator;
        $this->logger = $logger;
    }

    public function getGenerator()
    {
        return $this->generator;
    }

    public function proSubscription()
    {
        $proSub = $this->scopeConfigManager->getValue(
            'cdiscount_config/cdiscount_product/pro_sub'
        );
        return $proSub;
    }

    /**
     * Get default customer id
     *
     * @return bool|string
     */
    public function getDefaultCustomer()
    {
        $customer = false;
        $enabled = $this->scopeConfigManager->getValue('cdiscount_config/cdiscount_order/enable_default_customer');
        if ($enabled == 1) {
            $customer = $this->scopeConfigManager->getValue('cdiscount_config/cdiscount_order/default_customer');
        }
        return $customer;
    }

    public function getUserName()
    {
        $username = $this->userId = $this->scopeConfigManager
            ->getValue("cdiscount_config/cdiscount_setting/user_name");
        return $username;
    }

    public function getUserPassword()
    {
        $password = $this->apiKey = $this->scopeConfigManager
            ->getValue("cdiscount_config/cdiscount_setting/api_password");
        return $password;
    }

    public function getDebugMode()
    {
        $debugMode = $this->scopeConfigManager->getValue(
            'cdiscount_config/cdiscount_setting/debug_mode'
        );
        return $debugMode;
    }

    public function getShippingMethods()
    {
        $shippingMethod = $this->shippingMethods = $this->scopeConfigManager
            ->getValue("cdiscount_config/shipping_settings/shipping_methods");
        if (!empty($shippingMethod)) {
            $unserializedShippingMethods = @unserialize($shippingMethod);
            if ($unserializedShippingMethods) {
                $shippingMethod = $unserializedShippingMethods;
            }
            if (!is_array($shippingMethod)) {
                $shippingMethod = @json_decode($shippingMethod,true);
            }
        }
        if (empty($shippingMethod)) {
            $shippingMethod = [];
        }
        return $shippingMethod;
    }

    public function getConfAttrValues()
    {
        $attributes = $this->scopeConfigManager->getValue(
            'cdiscount_config/cdiscount_product/cdiscount_attributes'
        );
        if (empty($attributes)) {
            $attributes = [];
        } else {
            $attributes = explode(',', $attributes);
        }
        return $attributes;
    }

    public function getChunkSize()
    {
        $chunkSize = $this->scopeConfigManager->getValue(
            'cdiscount_config/cdiscount_product/chunk_product'
        );
        return $chunkSize;
    }

    public function getDefaultPreprationTime()
    {
        $defaultPreprationTime = $this->scopeConfigManager->getValue(
            'cdiscount_config/cdiscount_product/prepration_time'
        );
        if (empty($defaultPreprationTime)) {
            $defaultPreprationTime = 2;
        }
        return $defaultPreprationTime;
    }

    public function getPriceMapping()
    {
        $mappedForPrice = $this->scopeConfigManager->getValue(
            'cdiscount_config/cdiscount_product/price_map'
        );
        return $mappedForPrice;
    }

    public function getVat()
    {
        $vat = $this->scopeConfigManager->getValue(
            'cdiscount_config/cdiscount_product/vat'
        );
        if (empty($vat)) {
            $vat = 19.5;
        }
        return $vat;
    }
    public function getEco()
    {
        $ecoPart = $this->scopeConfigManager->getValue(
            'cdiscount_config/cdiscount_product/eco_part'
        );
        if (empty($ecoPart)) {
            $ecoPart = '0.0';
        }
        return $ecoPart;
    }
    public function getDea()
    {
        $dea = $this->scopeConfigManager->getValue(
            'cdiscount_config/cdiscount_product/dea_tax'
        );
        if (empty($dea)) {
            $dea = '0.0';
        }
        return $dea;
    }

    public function getDefaultProductCondition()
    {
        $defaultProductCondition = $this->scopeConfigManager->getValue(
            'cdiscount_config/cdiscount_product/product_condition'
        );
        return $defaultProductCondition;
    }

    public function getAllInvPriceStatus()
    {
        $allPriceInvCron = $this->scopeConfigManager->getValue(
            'cdiscount_config/cdiscount_cron/all_inventory_price_cron'
        );
        return $allPriceInvCron;
    }

    public function getOrderShipmentCron()
    {
        $orderShipmentCron = $this->scopeConfigManager->getValue(
            'cdiscount_config/cdiscount_cron/order_shipment_cron'
        );
        return $orderShipmentCron;
    }

    public function getPriceCronStatus()
    {
        $cronPrice = $this->scopeConfigManager->getValue(
            'cdiscount_config/cdiscount_cron/inventory_price_cron'
        );
        return $cronPrice;
    }

    public function getOrderCronStatus()
    {
        $cronOrder = $this->scopeConfigManager->getValue(
            'cdiscount_config/cdiscount_cron/order_cron'
        );
        return $cronOrder;
    }

    public function iscdav()
    {
        $cdav = $this->scopeConfigManager->getValue(
            'cdiscount_config/cdiscount_product/cdav'
        );
        if (empty($cdav) || $cdav == false) {
            $cdav = 'false';
        } else {
            $cdav = 'true';
        }
        return $cdav;
    }

    public function getApiConfig()
    {
        $config = false;
        try {
            //loading configurations

            $this->debugMode = $this->scopeConfigManager
                ->getValue('cdiscount_config/cdiscount_setting/debug_mode');
            $this->userId = $this->scopeConfigManager
                ->getValue("cdiscount_config/cdiscount_setting/user_name");
            $this->apiKey = $this->scopeConfigManager
                ->getValue("cdiscount_config/cdiscount_setting/api_password");

            $config = [
                'user_name' => $this->userId,
                'password' => $this->apiKey,
                'shipping_method' => $this->shippingMethod,
                'shipping_price' => $this->shippingPrice,
                'additional_shipping_price' => $this->additionalShippingPrice
            ];
        } catch (\Exception $exception) {
            $this->logger->error('Cdiscount: '.$exception->getMessage());
        }

        return $config;
    }

    public function isEnabled()
    {
        $enabled = $this->scopeConfigManager->getValue('cdiscount_config/cdiscount_setting/enable');
        return $enabled;
    }

    public function isValid()
    {
        $valid = $this->scopeConfigManager->getValue('cdiscount_config/cdiscount_setting/valid');
        return $valid;
    }

    public function validate()
    {
        $status = false;
        try {
            $userName = $this->getUserName();
            $password = $this->getUserPassword();
            $categories = $this->objectManager
                ->create('\Sdk\ApiClient\CDSApiClient', ['username' => $userName, 'password' => $password]);
            $token = $categories->init();
            if (isset($token) and $categories->isTokenValid()) {
                $status = true;
            }
        } catch (\Exception $exception) {
            $this->logger->error('Cdiscount:' . $exception->getMessage());
        }
        return $status;
    }

    /**
     * Get Mock mode for config
     *
     * @return bool
     */
    /*public function getMode()
    {
        $mode = $this->scopeConfigManager->getValue('cdiscount_config/cdiscount_setting/mode');
        return $mode;
    }*/

    public function getOrderIdPrefix()
    {
        $prefix = $this->scopeConfigManager
            ->getValue("cdiscount_config/cdiscount_order/order_id_prefix");
        if (isset($prefix) and !empty($prefix)) {
            return $prefix . '-';
        }
        return '';
    }

    public function getPriceType()
    {
        $priceType = $this->scopeConfigManager
            ->getValue("cdiscount_config/cdiscount_product/price_settings/price");
        if (isset($priceType) and !empty($priceType)) {
            return $priceType;
        }
        return '0';
    }

    public function getFixedPrice()
    {
        $fixPrice = $this->scopeConfigManager
            ->getValue("cdiscount_config/cdiscount_product/price_settings/fix_price");
        if (isset($fixPrice) and !empty($fixPrice)) {
            return $fixPrice;
        }
        return '0';
    }

    public function getPercentPrice()
    {
        $percentPrice = $this->scopeConfigManager
            ->getValue("cdiscount_config/cdiscount_product/price_settings/percentage_price");
        if (isset($percentPrice) and !empty($percentPrice)) {
            return $percentPrice;
        }
        return '0';
    }

    public function getDifferPrice()
    {
        $differPrice = $this->scopeConfigManager
            ->getValue("cdiscount_config/cdiscount_product/price_settings/different_price");
        if (isset($differPrice) and !empty($differPrice)) {
            return $differPrice;
        }
        return '0';
    }

    public function getStore()
    {
        $storeId = $this->scopeConfigManager
            ->getValue("cdiscount_config/cdiscount_setting/storeid");
        if (isset($storeId) and !empty($storeId)) {
            return $storeId;
        }
        return '0';
    }

    public function getThrottleMode()
    {
        return $this->throttle = $this->scopeConfigManager
            ->getValue("cdiscount_config/cdiscount_product/throttle");
    }
}
