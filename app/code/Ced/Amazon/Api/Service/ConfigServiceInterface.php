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
 * @package     Ced_2.3
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Api\Service;

interface ConfigServiceInterface
{
    const CONFIG_PATH_ENABLE = "amazon/settings/enable";

    const CONFIG_PATH_ORDER_ALERT_NOTIFICATION = "amazon/order/order_notification";
    const CONFIG_PATH_ORDER_NOTIFICATION = "amazon/order/order_notify_email_enable";
    const CONFIG_PATH_ORDER_STATUS = "amazon/order/status";
    const CONFIG_PATH_ORDER_NOTIFICATION_EMAIL = "amazon/order/order_notify_email";
    const CONFIG_PATH_AUTO_INVOICE = "amazon/order/auto_invoice";
    const CONFIG_PATH_US_TAX_IMPORT = "amazon/order/import_taxes_for_us_selected_regions";
    const CONFIG_PATH_SHIPPING_TAX_IMPORT = "amazon/order/import_shipping_taxes";
    const CONFIG_PATH_INCREMENT_ID_PREFIX = "amazon/order/order_id_prefix";
    const CONFIG_PATH_INCREMENT_ID_RULES = "amazon/order/increment_id_rules";
    const CONFIG_PATH_BACKORDER = "amazon/order/backorder";
    const CONFIG_PATH_ALTERNATE_SKU_ATTRIBUTE = "amazon/order/alternate_sku";
    const CONFIG_PATH_ORDER_IMPORT_TIME = "amazon/order/time";
    // Deprecated: Use increment_id_rules
    const CONFIG_PATH_PO_ID_SAME_AS_INCREMENT_ID = "amazon/order/po_id_as_increment_id";

    const CONFIG_PATH_ENABLE_GUEST_CUSTOMER = "amazon/order/guest";
    const CONFIG_PATH_ENABLE_DEFAULT_CUSTOMER = "amazon/order/enable_default_customer";
    const CONFIG_PATH_DEFAULT_CUSTOMER_EMAIL = "amazon/order/default_customer";
    const CONFIG_PATH_ENABLE_DEFAULT_BILLING_ADDRESS = "amazon/order/use_default_billing_address";
    const CONFIG_PATH_CREATE_REGION = "amazon/order/create_region";
    const CONFIG_PATH_USE_GEOCODE = "amazon/order/use_geocode";
    const CONFIG_PATH_USE_DASH = "amazon/order/use_dash";
    const CONFIG_PATH_TRACKING_NUMBER_REQUIRED = "amazon/order/tracking_number_required";
    const CONFIG_PATH_CREATE_UNAVAILABLE_PRODUCT = "amazon/order/create_unavailable_product";

    const CONFIG_PATH_PRODUCT_PROFILE_AUTO_UPLOAD = "amazon/product/profile/auto_upload";
    const CONFIG_PATH_PRODUCT_CHUNK_UPLOAD_QUEUE_SIZE = "amazon/product/chunk_settings/queue_size";
    const CONFIG_PATH_PRODUCT_CHUNK_PRICE_QUEUE_SIZE = "amazon/product/chunk_settings/price_queue_size";
    const CONFIG_PATH_PRODUCT_CHUNK_INVENTORY_QUEUE_SIZE = "amazon/product/chunk_settings/inventory_queue_size";
    const CONFIG_PATH_PRODUCT_CHUNK_STATUS_QUEUE_SIZE = "amazon/product/chunk_settings/status_queue_size";

    const CONFIG_PATH_PRODUCT_CHUNK_STATUS_FEED_SIZE = "amazon/product/chunk_settings/status_feed_size";
    const CONFIG_PATH_PRODUCT_CHUNK_PRICING_FEED_SIZE = "amazon/product/chunk_settings/price_feed_size";
    const CONFIG_PATH_PRODUCT_CHUNK_INVENTORY_FEED_SIZE = "amazon/product/chunk_settings/inventory_feed_size";
    const CONFIG_PATH_PRODUCT_CHUNK_PRODUCT_FEED_SIZE = "amazon/product/chunk_settings/feed_size";

    const CONFIG_PATH_ORDER_IMPORT = "amazon/cron/order_cron";
    const CONFIG_PATH_PRICE_SYNC = "amazon/cron/price_cron";
    const CONFIG_PATH_INVENTORY_SYNC = "amazon/cron/inventory_cron";

    const CONFIG_PATH_PRICE_ALLOW_SALE = "amazon/product/price/allow_sale_price";
    const CONFIG_PATH_PRICE_SALE_USE_DEFAULT_ATTRIBUTE = "amazon/product/price/use_default_sale_price";
    const CONFIG_PATH_PRICE_SALE_ATTRIBUTE = "amazon/product/price/sale_price_attribute";
    const CONFIG_PATH_PRICE_SALE_START_DATE = "amazon/product/price/sale_start_date";
    const CONFIG_PATH_PRICE_SALE_END_DATE = "amazon/product/price/sale_end_date";
    const CONFIG_PATH_SEND_PRICE_MARKETPLACE_WISE = "amazon/product/price/send_price_marketplace_wise";

    const CONFIG_PATH_PRICE_TYPE = "amazon/product/price/type";
    const CONFIG_PATH_PRICE_TYPE_FIXED = "amazon/product/price/fixed";
    const CONFIG_PATH_PRICE_TYPE_PERCENTAGE = "amazon/product/price/percentage";
    const CONFIG_PATH_PRICE_TYPE_ATTRIBUTE = "amazon/product/price/map_attribute";

    const CONFIG_PATH_INVENTORY_FULFILMENT_LATENCY = "amazon/product/inventory/fulfilment_latency";
    const CONFIG_PATH_INVENTORY_FULFILMENT_CHANNEL = "amazon/product/inventory/fulfilment_channel";
    const CONFIG_PATH_INVENTORY_OVERRIDE = "amazon/product/inventory/override_inventory";

    const CONFIG_PATH_INVENTORY_THRESHOLD_STATUS = "amazon/product/inventory/advanced_threshold_status";
    const CONFIG_PATH_INVENTORY_THRESHOLD_VALUE = "amazon/product/inventory/inventory_rule_threshold";
    const CONFIG_PATH_INVENTORY_THRESHOLD_LESS_THAN =
        "amazon/product/inventory/send_inventory_for_lesser_than_threshold";
    const CONFIG_PATH_INVENTORY_THRESHOLD_GREATER_THAN =
        "amazon/product/inventory/send_inventory_for_greater_than_threshold";
    const CONFIG_PATH_INVENTORY_MAP_ATTRIBUTE = "amazon/product/inventory/map_attribute";

    const CONFIG_PATH_THROTTLE_MODE = "amazon/developer/throttle";
    const CONFIG_PATH_SHIPMENT_ASYNC = "amazon/developer/shipment_async";
    const CONFIG_PATH_LOGGING_LEVEL = "amazon/developer/logging_level";

    /**
     * Get Alternate SKU Attribute
     * @return string|null
     */
    public function getAlternateSkuAttribute();

    /**
     * Get Default Order Import DateTime
     * @return string
     */
    public function getImportTime();

    /**
     * Get Guest Customer Flag
     * @return boolean
     */
    public function getGuestCustomer();

    /**
     * Get Default Customer Email
     * @return string|null
     */
    public function getDefaultCustomer();

    /**
     * Order Increment Id Rule: Same as Amazon Purchase Id
     * @return boolean
     */
    public function isOrderIdSameAsPoId();

    /**
     * Order Increment Id Rule: Prefix
     * @return string
     */
    public function getOrderIdPrefix();

    /**
     * Order Increment Id Rules
     * @return array
     */
    public function getIncrementIdRules();

    /**
     * Get logging level
     * @return integer
     */
    public function getLoggingLevel();

    /**
     * Inventory Sync Cron
     * @return bool
     */
    public function getInventorySync();

    /**
     * Auto Invoice After Import
     * @return bool
     */
    public function getAutoInvoice();

    /**
     * Get Notification Email
     * @return string
     */
    public function getNotificationEmail();

    /**
     * US Tax import for 3 regions only
     * @return bool
     */
    public function getUSTaxImport();

    /**
     * Import Shipping Tax
     * @return bool
     */
    public function getShippingTaxImport();

    /**
     * @return mixed
     */
    public function getInventoryAttribute();

    /**
     * Enable to create backorder on inventory not available
     * @return bool
     */
    public function createBackorder();

    /**
     * Get Use Default Billing Address
     * @return bool
     */
    public function getUseDefaultBilling();

    /**
     * Create Region/State on order import
     * @return bool
     */
    public function createRegion();

    /**
     * Use (-) on Region/State not available in order address
     * @return bool
     */
    public function useDash();

    /**
     * Use Google Geo code API to validate order address
     * @return bool
     */
    public function useGeocode();

    /**
     * Add Notification
     * @return boolean
     */
    public function addNotification();

    /**
     * Create Product on Order Import
     * @return boolean
     */
    public function createUnavailableProduct();

    /**
     * Send Price Feed marketplace wise
     * @return boolean
     */
    public function sendPriceFeedMPWise();
    
    /**
     * Auto Upload Products to Amazon on Assignment to Profile
     * @return boolean
     */
    public function autoUploadOnAdd();
}
