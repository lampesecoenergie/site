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
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Service;

use Amazon\Sdk\Api\ConfigFactory as AmazonConfigFactory;
use Ced\Amazon\Api\Service\ConfigServiceInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\DirectoryList;

class Config implements ConfigServiceInterface
{
    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfigManager;

    /**
     * @var DirectoryList
     */
    public $dl;

    /**
     * @var string $sellerId
     */
    public $sellerId;

    /**
     * @var array $marketplaceIds
     */
    public $marketplaceIds;

    /**
     * @var string $accessKeyId
     */
    public $accessKeyId;

    /**
     * @var string $secretKey
     */
    public $secretKey;

    /**
     * @var string $serviceUrl
     */
    public $serviceUrl;

    /**
     * Debug Log Mode
     * @var boolean
     */
    public $debugMode = true;

    /**
     * @var AmazonConfigFactory
     */
    public $config;

    /** @var boolean */
    public $throttle;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param DirectoryList $directoryList
     * @param AmazonConfigFactory $config
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DirectoryList $directoryList,
        AmazonConfigFactory $config
    ) {
        $this->scopeConfigManager = $scopeConfig;
        $this->dl = $directoryList;
        $this->config = $config;
    }

    /**
     * @deprecated: use account level config
     * Get Mock mode for config
     * @return bool
     */
    public function getMode()
    {
        $mock = false;
        $mode = $this->scopeConfigManager->getValue('amazon/settings/mode');
        if ($mode == 'mock') {
            $mock = true;
        }
        return $mock;
    }

    /**
     * Get default customer id
     * @return bool|string
     */
    public function getDefaultCustomer()
    {
        $customer = false;
        $enabled = $this->scopeConfigManager->getValue(self::CONFIG_PATH_ENABLE_DEFAULT_CUSTOMER);
        if ($enabled == '1') {
            $customer = $this->scopeConfigManager->getValue(self::CONFIG_PATH_DEFAULT_CUSTOMER_EMAIL);
        }
        return $customer;
    }

    /**
     * Get default customer id for billing address
     * @return bool|string
     */
    public function getUseDefaultBilling()
    {
        $use = false;
        $enabled = $this->scopeConfigManager->getValue(self::CONFIG_PATH_ENABLE_DEFAULT_CUSTOMER);
        if ($enabled) {
            $use = $this->scopeConfigManager->getValue(self::CONFIG_PATH_ENABLE_DEFAULT_BILLING_ADDRESS);
        }

        return $use;
    }

    /**
     * Prepare config for api with data given.
     * Default marketplace is 0 index, or the set 'marketplace_id'
     * @param \Magento\Framework\DataObject $data
     * @return \Amazon\Sdk\Api\Config
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function prepare(\Magento\Framework\DataObject $data)
    {
        $marketplaceIds = $data->getData(\Ced\Amazon\Model\Account::COLUMN_MARKETPLACE);
        $marketplaceIds = is_string($marketplaceIds) ? explode(",", $marketplaceIds) : $marketplaceIds;
        $token = $data->getData(\Ced\Amazon\Model\Account::COLUMN_AWS_AUTH_ID);

        $accessKeyId = $data->getData(\Ced\Amazon\Model\Account::COLUMN_ACCESS_KEY_ID);
        $secretKey = $data->getData(\Ced\Amazon\Model\Account::COLUMN_SECRET_KEY);
        $cedcommerce = $data->getData(\Ced\Amazon\Model\Account::COLUMN_CEDCOMMERCE);

        if ($cedcommerce) {
            foreach ($marketplaceIds as $marketplaceId) {
                $accessKeyId = \Ced\Amazon\Model\Account::getCedcommerceAccessKeyId($marketplaceId);
                $secretKey = \Ced\Amazon\Model\Account::getCedcommerceSecretKey($marketplaceId);
                if (isset($accessKeyId, $secretKey)) {
                    break;
                }
            }
        }

        $params = [
            'params' => [
                'sellerId' => $data->getData(\Ced\Amazon\Model\Account::COLUMN_SELLER_ID),
                'marketplaceId' => $marketplaceIds, // array of ids
                'accessKeyId' => $accessKeyId,
                'secretKey' => $secretKey,
                'muteLog' => !$this->getDebug()
            ]
        ];

        if (!empty($token)) {
            $params['params']['token'] = $token;
        }

        /**
         * @var \Amazon\Sdk\Api\Config
         */
        $config = $this->config->create($params);

        return $config;
    }

    public function getThrottleMode()
    {
        return $this->throttle = $this->scopeConfigManager->getValue(self::CONFIG_PATH_THROTTLE_MODE);
    }

    public function getShipmentMode()
    {
        $mode = $this->scopeConfigManager->getValue(self::CONFIG_PATH_SHIPMENT_ASYNC);
        if ($mode != "0") {
            $mode = true;
        }

        return $mode;
    }

    public function getOrderIdPrefix()
    {
        $prefix = $this->scopeConfigManager->getValue(self::CONFIG_PATH_INCREMENT_ID_PREFIX);
        if (isset($prefix) && !empty($prefix)) {
            return $prefix . '-';
        }

        return '';
    }

    /**
     * Get Is OrderId Same As PoId
     * @return boolean
     */
    public function isOrderIdSameAsPoId()
    {
        $flag = false;
        $rules = $this->getIncrementIdRules();
        if (in_array(\Ced\Amazon\Model\Source\Order\Config\IncrementId::ADD_AMAZON_ORDER_ID, $rules)) {
            $flag = true;
        }

        return $flag;
    }

    public function getStore()
    {
        $storeId = $this->scopeConfigManager->getValue('amazon/settings/storeid');
        return $storeId;
    }

    /**
     * Get notification email if enabled
     * @return string|null
     */
    public function getNotificationEmail()
    {
        $email = null;
        $enable = $this->scopeConfigManager->getValue(self::CONFIG_PATH_ORDER_NOTIFICATION);
        if ($enable) {
            $email = $this->scopeConfigManager->getValue(self::CONFIG_PATH_ORDER_NOTIFICATION_EMAIL);
        }

        return $email;
    }

    public function getDebug()
    {
        $this->debugMode = $this->scopeConfigManager
            ->getValue('amazon/developer/debug_mode');
        if (!isset($this->debugMode)) {
            $this->debugMode = true;
        }

        return $this->debugMode;
    }

    /**
     * Get auto invoice enable
     * @return bool|mixed
     */
    public function getAutoInvoice()
    {
        $autoInvoice = $this->scopeConfigManager->getValue(self::CONFIG_PATH_AUTO_INVOICE);
        if (isset($autoInvoice) && empty($autoInvoice)) {
            $autoInvoice = false;
        }
        return $autoInvoice;
    }

    public function getAutoCancellation()
    {
        $autoReject = $this->scopeConfigManager
            ->getValue("amazon/order/auto_cancellation");
        if (isset($autoReject) && empty($autoReject)) {
            $autoReject = false;
        }
        return $autoReject;
    }

    public function getAutoAcknowledgement()
    {
        $ack = $this->scopeConfigManager
            ->getValue("amazon/order/auto_acknowledgement");
        if (isset($ack) && empty($ack)) {
            $ack = false;
        }
        return $ack;
    }

    public function getInventoryLatency()
    {
        $latency = $this->scopeConfigManager
            ->getValue(self::CONFIG_PATH_INVENTORY_FULFILMENT_LATENCY);
        if (!isset($latency) || empty($latency)) {
            $latency = '1';
        }
        return $latency;
    }

    public function getFulfilmentChannel()
    {
        $channel = $this->scopeConfigManager
            ->getValue(self::CONFIG_PATH_INVENTORY_FULFILMENT_CHANNEL);
        if (!isset($channel) || empty($channel)) {
            $channel = \Ced\Amazon\Model\Source\Product\Inventory\Channel::TYPE_MFN;
        }

        return $channel;
    }

    public function getInventoryOverride()
    {
        $flag = $this->scopeConfigManager
            ->getValue(self::CONFIG_PATH_INVENTORY_OVERRIDE);

        return $flag;
    }

    /**
     * Get Feed Chunk Size, default 100
     * @param string $type
     * @return int
     */
    public function getFeedSize($type = \Amazon\Sdk\Api\Feed::PRODUCT)
    {
        switch ($type) {
            case \Amazon\Sdk\Api\Feed::PRODUCT:
                $chunkSize = $this->scopeConfigManager
                    ->getValue(self::CONFIG_PATH_PRODUCT_CHUNK_PRODUCT_FEED_SIZE);
                break;
            case \Amazon\Sdk\Api\Feed::PRODUCT_INVENTORY:
                $chunkSize = $this->scopeConfigManager
                    ->getValue(self::CONFIG_PATH_PRODUCT_CHUNK_PRICING_FEED_SIZE);
                break;
            case \Amazon\Sdk\Api\Feed::PRODUCT_PRICING:
                $chunkSize = $this->scopeConfigManager
                    ->getValue(self::CONFIG_PATH_PRODUCT_CHUNK_INVENTORY_FEED_SIZE);
                break;
            case \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_OPEN_LISTING_ALL_DATA:
                $chunkSize = $this->scopeConfigManager
                    ->getValue(self::CONFIG_PATH_PRODUCT_CHUNK_STATUS_FEED_SIZE);
                break;
            default:
                $chunkSize = 100;
        }

        if (!isset($chunkSize) || empty($chunkSize)) {
            $chunkSize = 100;
        }
        return $chunkSize;
    }

    /**
     * Get Queue Chunk Size, default 100
     * @param string $type
     * @return int
     */
    public function getQueueSize($type = \Amazon\Sdk\Api\Feed::PRODUCT)
    {
        switch ($type) {
            case \Amazon\Sdk\Api\Feed::PRODUCT:
                $chunkSize = $this->scopeConfigManager
                    ->getValue(self::CONFIG_PATH_PRODUCT_CHUNK_UPLOAD_QUEUE_SIZE);
                break;
            case \Amazon\Sdk\Api\Feed::PRODUCT_INVENTORY:
                $chunkSize = $this->scopeConfigManager
                    ->getValue(self::CONFIG_PATH_PRODUCT_CHUNK_INVENTORY_QUEUE_SIZE);
                break;
            case \Amazon\Sdk\Api\Feed::PRODUCT_PRICING:
                $chunkSize = $this->scopeConfigManager
                    ->getValue(self::CONFIG_PATH_PRODUCT_CHUNK_PRICE_QUEUE_SIZE);
                break;
            case \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_OPEN_LISTING_ALL_DATA:
                $chunkSize = $this->scopeConfigManager
                    ->getValue(self::CONFIG_PATH_PRODUCT_CHUNK_STATUS_QUEUE_SIZE);
                break;
            default:
                $chunkSize = 100;
        }

        if (!isset($chunkSize) || empty($chunkSize)) {
            $chunkSize = 100;
        }

        return $chunkSize;
    }

    public function getPriceSync()
    {
        $state = $this->scopeConfigManager->getValue(self::CONFIG_PATH_PRICE_SYNC);
        return $state;
    }

    public function getInventorySync()
    {
        $state = $this->scopeConfigManager->getValue(self::CONFIG_PATH_INVENTORY_SYNC);
        return $state;
    }

    public function getOrderImport()
    {
        $state = $this->scopeConfigManager->getValue(self::CONFIG_PATH_ORDER_IMPORT);
        return $state;
    }

    public function getLoggingLevel()
    {
        $level = $this->scopeConfigManager->getValue(self::CONFIG_PATH_LOGGING_LEVEL);
        return $level;
    }

    /**
     * @return bool
     * @deprecated
     */
    public function isValid()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function getInventoryObserver()
    {
        return true;
    }

    public function getReportDirectory()
    {
        $path = $this->dl->getPath('var');
        return $path;
    }

    public function getReportFile($name)
    {
        $path = $this->getReportDirectory();
        $path .= DIRECTORY_SEPARATOR . "amazon" . DIRECTORY_SEPARATOR . "report" . DIRECTORY_SEPARATOR . $name;
        return $path;
    }

    public function getAllowSalePrice()
    {
        $type = $this->scopeConfigManager->getValue(self::CONFIG_PATH_PRICE_ALLOW_SALE);
        return $type;
    }

    public function getPriceType()
    {
        $type = trim((string)$this->scopeConfigManager->getValue(self::CONFIG_PATH_PRICE_TYPE));
        if (empty($type)) {
            $type = \Ced\Amazon\Model\Source\Config\Price::TYPE_DEFAULT;
        }
        return $type;
    }

    public function getPriceFixed()
    {
        $fixed = trim((string)$this->scopeConfigManager->getValue(self::CONFIG_PATH_PRICE_TYPE_FIXED));
        return $fixed;
    }

    public function getPricePercentage()
    {
        $percentage = trim((string)$this->scopeConfigManager->getValue(self::CONFIG_PATH_PRICE_TYPE_PERCENTAGE));
        return $percentage;
    }

    /**
     * Get Price Mapping
     * @return array
     */
    public function getPriceAttribute()
    {
        $mappings = trim((string)$this->scopeConfigManager->getValue(self::CONFIG_PATH_PRICE_TYPE_ATTRIBUTE));
        $mappings = json_decode($mappings, true);
        $mappings = !empty($mappings) && is_array($mappings) ? $mappings : [];
        $values = [];
        foreach ($mappings as $mapping) {
            if (isset($mapping['marketplace'], $mapping['attribute'])) {
                $values[$mapping['marketplace']] = $mapping['attribute'];
            }
        }

        return $values;
    }

    public function getPriceAttributeList()
    {
        $attributes = $this->getPriceAttribute();
        $attributeList = array_values($attributes);
        return array_unique($attributeList);
    }

    public function getShippingTaxImport()
    {
        $flag = $this->scopeConfigManager->getValue(self::CONFIG_PATH_SHIPPING_TAX_IMPORT);
        if (empty($flag)) {
            $flag = false;
        }

        return $flag;
    }

    public function getInventoryThresholdStatus()
    {
        $flag = $this->scopeConfigManager->getValue(self::CONFIG_PATH_INVENTORY_THRESHOLD_STATUS);
        if (empty($flag)) {
            $flag = false;
        }

        return $flag;
    }

    public function getInventoryThresholdValue()
    {
        $flag = $this->scopeConfigManager->getValue(self::CONFIG_PATH_INVENTORY_THRESHOLD_VALUE);
        if (empty($flag)) {
            $flag = 0;
        }

        return $flag;
    }

    public function getInventoryThresholdLessThan()
    {
        $flag = $this->scopeConfigManager->getValue(self::CONFIG_PATH_INVENTORY_THRESHOLD_LESS_THAN);

        return $flag;
    }

    public function getInventoryThresholdGreaterThan()
    {
        $flag = $this->scopeConfigManager->getValue(self::CONFIG_PATH_INVENTORY_THRESHOLD_GREATER_THAN);

        return $flag;
    }

    public function useGeocode()
    {
        $flag = $this->scopeConfigManager->getValue(self::CONFIG_PATH_USE_GEOCODE);

        return $flag;
    }

    public function useDash()
    {
        $flag = $this->scopeConfigManager->getValue(self::CONFIG_PATH_USE_DASH);

        return $flag;
    }

    public function createRegion()
    {
        $flag = $this->scopeConfigManager->getValue(self::CONFIG_PATH_CREATE_REGION);

        return $flag;
    }

    /**
     * Create Backorder if product qty is 'out of stock' or 'less than the order qty'
     * @return boolean
     */
    public function createBackorder()
    {
        $flag = (boolean)$this->scopeConfigManager->getValue(self::CONFIG_PATH_BACKORDER);

        return $flag;
    }

    /**
     * Default order status for import
     * @return array
     */
    public function getOrderStatus()
    {
        $statusList = [
            \Ced\Amazon\Model\Source\Order\Status::UNSHIPPED,
            \Ced\Amazon\Model\Source\Order\Status::PARTIALLY_SHIPPED,
        ];

        $status = (string)$this->scopeConfigManager->getValue(self::CONFIG_PATH_ORDER_STATUS);
        if (!empty($status)) {
            $tmp = explode(",", $status);
            if (!empty($tmp) && is_array($tmp)) {
                $statusList = $tmp;
            }
        }

        return $statusList;
    }

    /**
     * Increment Id Rules
     * @return array
     */
    public function getIncrementIdRules()
    {
        $ruleList = [];

        $rules = (string)$this->scopeConfigManager->getValue(self::CONFIG_PATH_INCREMENT_ID_RULES);
        if (!empty($rules)) {
            $tmp = explode(",", $rules);
            if (!empty($tmp) && is_array($tmp)) {
                $ruleList = $tmp;
            }
        }

        return $ruleList;
    }

    /**
     * Get Alternate SKU Attribute
     * @return string|null
     */
    public function getAlternateSkuAttribute()
    {
        $attribute = (string)$this->scopeConfigManager->getValue(self::CONFIG_PATH_ALTERNATE_SKU_ATTRIBUTE);
        if ($attribute == \Ced\Amazon\Model\Source\Attribute::DEFAULT_VALUE) {
            $attribute = null;
        }

        return $attribute;
    }

    /**
     * Get Order Import Time
     * @return string|null
     */
    public function getImportTime()
    {
        $time = (string)$this->scopeConfigManager->getValue(self::CONFIG_PATH_ORDER_IMPORT_TIME);
        return $time;
    }

    /**
     * Get Inventory Mapping
     * @return array
     */
    public function getInventoryAttribute()
    {
        $inventoryMapAttribute = (string)$this->scopeConfigManager->getValue(self::CONFIG_PATH_INVENTORY_MAP_ATTRIBUTE);
        $mappings = json_decode($inventoryMapAttribute, true);
        $mappings = !empty($mappings) && is_array($mappings) ? $mappings : [];
        $values = [];
        foreach ($mappings as $mapping) {
            if (isset($mapping['account'], $mapping['attribute'])) {
                $values[$mapping['account']] = $mapping['attribute'];
            }
        }

        return $values;
    }

    /**
     * Get Inventory Attribute List for All Accounts
     * @return array
     */
    public function getInventoryAttributeList()
    {
        $attributes = $this->getInventoryAttribute();
        $attributeList = array_values($attributes);
        return array_unique($attributeList);
    }

    public function getUSTaxImport()
    {
        $flag = $this->scopeConfigManager->getValue(self::CONFIG_PATH_US_TAX_IMPORT);
        if (empty($flag)) {
            $flag = false;
        }

        return $flag;
    }

    public function getGuestCustomer()
    {
        $flag = $this->scopeConfigManager->getValue(self::CONFIG_PATH_ENABLE_GUEST_CUSTOMER);
        if (empty($flag)) {
            $flag = false;
        }

        return $flag;
    }

    /**
     * Is Tracking Number Required
     * @return bool
     */
    public function isTrackingNumberRequired()
    {
        $flag = $this->scopeConfigManager->getValue(self::CONFIG_PATH_TRACKING_NUMBER_REQUIRED);
        if (empty($flag)) {
            $flag = false;
        }

        return $flag;
    }

    /**
     * Add Notification
     * @return boolean
     */
    public function addNotification()
    {
        $flag = (boolean)$this->scopeConfigManager->getValue(self::CONFIG_PATH_ORDER_ALERT_NOTIFICATION);

        return $flag;
    }

    public function createUnavailableProduct()
    {
        $flag = $this->scopeConfigManager->getValue(self::CONFIG_PATH_CREATE_UNAVAILABLE_PRODUCT);
        if (empty($flag)) {
            $flag = false;
        }

        return $flag;
    }

    public function sendPriceFeedMPWise()
    {
        $flag = $this->scopeConfigManager->getValue(self::CONFIG_PATH_SEND_PRICE_MARKETPLACE_WISE);
        if (empty($flag)) {
            $flag = false;
        }

        return $flag;
    }

    /**
     * Auto Upload Products to Amazon on Assignment to Profile
     * @return boolean
     */
    public function autoUploadOnAdd()
    {
        $flag = (boolean)$this->scopeConfigManager->getValue(self::CONFIG_PATH_PRODUCT_PROFILE_AUTO_UPLOAD);

        return $flag;
    }
}
