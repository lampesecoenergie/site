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
 * @package   Ced_RueDuCommerce
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RueDuCommerce\Helper;

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

    /**
     * @var \RueDuCommerceSdk\Api\ConfigFactory
     */
    public $config;

    public $generator;

    /**
     * Config constructor.
     *
     * @param Context                                     $context
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\ObjectManagerInterface   $objectManager
     * @param \Magento\Framework\Xml\Generator            $generator
     * @param \RueDuCommerceSdk\Api\ConfigFactory               $config
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Xml\Generator $generator,
        \RueDuCommerceSdk\Core\ConfigFactory $config
    ) {
        parent::__construct($context);
        $this->scopeConfigManager = $context->getScopeConfig();
        $this->objectManager = $objectManager;
        $this->dl = $directoryList;
        $this->config = $config;
        $this->generator = $generator;
    }

    /**
     * Get default customer id
     *
     * @return bool|string
     */
    public function getDefaultCustomer()
    {
        $customer = false;
        $enabled = $this->scopeConfigManager->getValue('rueducommerce_config/rueducommerce_order/enable_default_customer');
        if ($enabled == 1) {
            $customer = $this->scopeConfigManager->getValue('rueducommerce_config/rueducommerce_order/default_customer_email');
        }
        return $customer;
    }

    /**
     * @return \RueDuCommerceSdk\Api\Config|boolean
     */
    public function getApiConfig($indication = 'else')
    {
        $config = false;
        try {
            $mode = $this->getMode();
            
            //loading configurations
            $this->debugMode = $this->scopeConfigManager
                ->getValue('rueducommerce_config/rueducommerce_setting/debug_mode');
            $this->userId = $this->scopeConfigManager
                ->getValue("rueducommerce_config/rueducommerce_setting/user_id");
            if ($indication == 'order') {
                $this->apiKey = $this->scopeConfigManager
                    ->getValue("rueducommerce_config/rueducommerce_setting/order_api_key");
            } else {
                $this->apiKey = $this->scopeConfigManager
                    ->getValue("rueducommerce_config/rueducommerce_setting/api_key");
            }
            $this->endpoint = $this->scopeConfigManager
                ->getValue("rueducommerce_config/rueducommerce_setting/{$mode}_endpoint");
            /**
             * @var \RueDuCommerceSdk\Api\Config
             */
            $config = $this->config->create(
                [
                'params' => [
                    'userId' => $this->userId,
                    'apiKey' => $this->apiKey,
                    'apiUrl' => $this->endpoint,
                    'debugMode' => $this->debugMode,
                    'baseDirectory' => $this->dl->getPath('var') . DS . 'rueducommerce',
                    'generator' => $this->generator,
                ]
                ]
            );
        } catch (\Exception $exception) {
            $this->_logger->error('RueDuCommerce: '.$exception->getMessage());
        }

        return $config;
    }

    public function isEnabled()
    {
        $enabled = $this->scopeConfigManager->getValue('rueducommerce_config/rueducommerce_setting/enable');
        return $enabled;
    }

    public function isValid()
    {
        $valid = $this->scopeConfigManager->getValue('rueducommerce_config/rueducommerce_setting/valid');
        return $valid;
    }

    public function validate()
    {
        $status = false;
        try {
            $config = $this->getApiConfig();
            
            $categories = $this->objectManager
                ->create('\RueDuCommerceSdk\Product', ['config' => $config])
                ->getCategories(['hierarchy'=>'ACCESS','max_level'=>1], true);
            if (isset($categories) and !empty($categories)) {
                $status = true;
            }
        } catch (\Exception $exception) {
            $this->_logger->error('RueDuCommerce:' . $exception->getMessage());
        }
        return $status;
    }

    /**
     * Get Mock mode for config
     *
     * @return bool
     */
    public function getMode()
    {
        $mode = $this->scopeConfigManager->getValue('rueducommerce_config/rueducommerce_setting/mode');
        return $mode;
    }

    public function getOrderSyncCron()
    {
        $orderSyncCron = $this->scopeConfigManager->getValue('rueducommerce_config/rueducommerce_cron/order_sync_cron');
        return $orderSyncCron;
    }

    public function getOrderCron()
    {
        $orderCron = $this->scopeConfigManager->getValue('rueducommerce_config/rueducommerce_cron/order_cron');
        return $orderCron;
    }

    public function getOrderShipmentCron()
    {
        $orderShipmentCron = $this->scopeConfigManager->getValue('rueducommerce_config/rueducommerce_cron/order_shipment_cron');
        return $orderShipmentCron;
    }

    public function getInventoryPriceCron()
    {
        $invCron = $this->scopeConfigManager->getValue('rueducommerce_config/rueducommerce_cron/inventory_price_cron');
        return $invCron;
    }

    public function getFeedSyncCron()
    {
        $feedCron = $this->scopeConfigManager->getValue('rueducommerce_config/rueducommerce_cron/feed_sync_cron');
        return $feedCron;
    }

    public function getFullOfferSyncCron()
    {
        $fullOfferCron = $this->scopeConfigManager->getValue('rueducommerce_config/rueducommerce_cron/full_offer_sync_cron');
        return $fullOfferCron;
    }

    public function getRefundOnRueDuCommerce()
    {
        $refundOnRueDuCommerce = $this->scopeConfigManager->getValue('rueducommerce_config/rueducommerce_order/rueducommerce_refund_from_core');
        return $refundOnRueDuCommerce;
    }

    public function getRefundReason()
    {
        $refundReason = $this->scopeConfigManager->getValue('rueducommerce_config/rueducommerce_order/rueducommerce_refund_reason');
        return $refundReason;
    }

    public function getCreditMemoOnMagento()
    {
        $creditOnMagento = $this->scopeConfigManager->getValue('rueducommerce_config/rueducommerce_order/rueducommerce_creditmemo_on_magento');
        return $creditOnMagento;
    }

    public function getFromParentAttributes()
    {
        $fromParentAttrs = array();
        $parentAttrs = $this->scopeConfigManager->getValue("rueducommerce_config/rueducommerce_product/rueducommerce_other_prod_setting/rueducommerce_use_other_parent");
        $fromParentAttrs = explode(',', $parentAttrs);
        return $fromParentAttrs;
    }

    public function getMergeParentImages()
    {
        $mergeImages = $this->scopeConfigManager->getValue("rueducommerce_config/rueducommerce_product/rueducommerce_other_prod_setting/rueducommerce_merge_parent_images");
        return $mergeImages;
    }

    public function getSkipValidationAttributes()
    {
        $skipFromValidation = array();
        $skipAttr = $this->scopeConfigManager->getValue("rueducommerce_config/rueducommerce_product/rueducommerce_other_prod_setting/rueducommerce_skip_from_validation");
        $skipFromValidation = explode(',', $skipAttr);
        return $skipFromValidation;
    }

    public function getConfigAsSimple()
    {
        $uploadAsSimple = $this->scopeConfigManager->getValue("rueducommerce_config/rueducommerce_product/rueducommerce_other_prod_setting/rueducommerce_upload_config_as_simple");
        return $uploadAsSimple;
    }

    public function getOrderIdPrefix()
    {
        $prefix = $this->scopeConfigManager
            ->getValue("rueducommerce_config/rueducommerce_order/order_id_prefix");
        if (isset($prefix) and !empty($prefix)) {
            return $prefix;
        }
        return '';
    }

    public function getPriceType()
    {
        $priceType = $this->scopeConfigManager
            ->getValue("rueducommerce_config/rueducommerce_product/price_settings/price");
        if (isset($priceType) and !empty($priceType)) {
            return $priceType;
        }
        return '0';
    }

    public function getFixedPrice()
    {
        $fixPrice = $this->scopeConfigManager
            ->getValue("rueducommerce_config/rueducommerce_product/price_settings/fix_price");
        if (isset($fixPrice) and !empty($fixPrice)) {
            return $fixPrice;
        }
        return '0';
    }

    public function getPercentPrice()
    {
        $percentPrice = $this->scopeConfigManager
            ->getValue("rueducommerce_config/rueducommerce_product/price_settings/percentage_price");
        if (isset($percentPrice) and !empty($percentPrice)) {
            return $percentPrice;
        }
        return '0';
    }

    public function getDifferPrice()
    {
        $differPrice = $this->scopeConfigManager
            ->getValue("rueducommerce_config/rueducommerce_product/price_settings/different_price");
        if (isset($differPrice) and !empty($differPrice)) {
            return $differPrice;
        }
        return '0';
    }

    public function getReferenceType()
    {
        $product_reference_type = $this->scopeConfigManager
            ->getValue("rueducommerce_config/rueducommerce_setting/product_reference_type");
        if (isset($product_reference_type) and !empty($product_reference_type)) {
            return $product_reference_type;
        }
        return '0';
    }

    public function getReferenceValue()
    {
        $product_reference_value = $this->scopeConfigManager
            ->getValue("rueducommerce_config/rueducommerce_setting/product_reference_value");
        if (isset($product_reference_value) and !empty($product_reference_value)) {
            return $product_reference_value;
        }
        return '0';
    }

    public function getAutoCancelOrderSetting()
    {
        $auto_cancel_order = $this->scopeConfigManager
            ->getValue("rueducommerce_config/rueducommerce_order/auto_cancel_order");
        if (isset($auto_cancel_order) and !empty($auto_cancel_order)) {
            return $auto_cancel_order;
        }
        return '0';
    }

    public function getAutoAcceptOrderSetting()
    {
        $auto_cancel_order = $this->scopeConfigManager
            ->getValue("rueducommerce_config/rueducommerce_order/auto_accept_order");
        if (isset($auto_cancel_order) and !empty($auto_cancel_order)) {
            return $auto_cancel_order;
        }
        return '0';
    }

    public function getStore()
    {
        $storeId = $this->scopeConfigManager
            ->getValue("rueducommerce_config/rueducommerce_setting/storeid");
        if (isset($storeId) and !empty($storeId)) {
            return $storeId;
        }
        return '0';
    }

    public function getThrottleMode()
    {
        return $this->throttle = $this->scopeConfigManager
            ->getValue("rueducommerce_config/rueducommerce_product/throttle");
    }
        public function getThresholdStatus()
    {
        return $this->throttle = $this->scopeConfigManager
            ->getValue("rueducommerce_config/rueducommerce_product/inventory_settings/advanced_threshold_status");
    }
    public function getThresholdLimit()
    {
        return $this->throttle = $this->scopeConfigManager
            ->getValue("rueducommerce_config/rueducommerce_product/inventory_settings/inventory_rule_threshold");
    }
    public function getThresholdLimitMin()
    {
        return $this->throttle = $this->scopeConfigManager
            ->getValue("rueducommerce_config/rueducommerce_product/inventory_settings/send_inventory_for_lesser_than_threshold");
    }
    public function getThresholdLimitMax()
    {
        return $this->throttle = $this->scopeConfigManager
            ->getValue("rueducommerce_config/rueducommerce_product/inventory_settings/send_inventory_for_greater_than_threshold");
    }
}
