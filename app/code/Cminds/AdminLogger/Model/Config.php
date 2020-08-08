<?php

namespace Cminds\AdminLogger\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config
 *
 * @package Cminds\AdminLogger\Model
 */
class Config
{
    /**
     * Action types.
     */
    const ACTION_ADMIN_LOGIN_SUCCESS = 'admin_login_success';
    const ACTION_ADMIN_LOGIN_FAILED = 'admin_login_failed';
    const ACTION_ADMIN_PASSWORD_CHANGE_REQUEST = 'admin_password_change_request';

    const ACTION_PAGE_VIEW = 'page_view';
    const ACTION_CONFIGURATION_UPDATE = 'configuration_update';

    const ACTION_PRODUCT_CREATE = 'product_create';
    const ACTION_PRODUCT_UPDATE = 'product_update';
    const ACTION_PRODUCT_DELETE = 'product_delete';

    const ACTION_CATEGORY_CREATE = 'category_create';
    const ACTION_CATEGORY_UPDATE = 'category_update';
    const ACTION_CATEGORY_DELETE = 'category_delete';

    const ACTION_CUSTOMER_CREATE = 'customer_create';
    const ACTION_CUSTOMER_UPDATE = 'customer_update';
    const ACTION_CUSTOMER_DELETE = 'customer_delete';

    const ACTION_CONTENT_PAGE_CREATE = 'content_page_create';
    const ACTION_CONTENT_PAGE_UPDATE = 'content_page_update';
    const ACTION_CONTENT_PAGE_DELETE = 'content_page_delete';

    const ACTION_CONTENT_BLOCK_CREATE = 'content_block_create';
    const ACTION_CONTENT_BLOCK_UPDATE = 'content_block_update';
    const ACTION_CONTENT_BLOCK_DELETE = 'content_block_delete';

    const ACTION_CONTENT_WIDGET_CREATE = 'content_widget_create';
    const ACTION_CONTENT_WIDGET_UPDATE = 'content_widget_update';
    const ACTION_CONTENT_WIDGET_DELETE = 'content_widget_delete';

    const ACTION_ORDER_INVOICE_CREATE = 'order_invoice_create';
    const ACTION_ORDER_CREDITMEMO_CREATE = 'order_creditmemo_create';
    const ACTION_ORDER_SHIPMENT_CREATE = 'order_shipment_create';
    const ACTION_ORDER_BILLING_ADDRESS_UPDATE = 'order_billing_address_update';
    const ACTION_ORDER_SHIPPING_ADDRESS_UPDATE = 'order_shipping_address_update';
    const ACTION_ORDER_STATUS_UPDATE = 'order_status_update';
    const ACTION_ORDER_COMMENT_ADD = 'order_comment_add';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var array
     */
    private $config = [];

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface  $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;

        $this->storeId = $this->getStoreId();
    }

    /**
     * Get store id.
     *
     * @return int
     */
    private function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get module config value.
     *
     * @param $fieldKey
     *
     * @return mixed
     */
    private function getConfigValue($fieldKey)
    {
        if (isset($this->config[$fieldKey]) === false) {
            $this->config[$fieldKey] = $this->scopeConfig->getValue(
                'admin_logger_config/' . $fieldKey,
                ScopeInterface::SCOPE_STORE,
                $this->storeId
            );
        }

        return $this->config[$fieldKey];
    }

    /**
     * Return bool value depends of that if module is active or not.
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getConfigValue('general/enable');
    }

    /**
     * Check is page view logging is enabled.
     *
     * @return bool
     */
    public function isPageViewLoggingEnabled()
    {
        return (bool)$this->getConfigValue('logs_settings/page_view_logging_enabled');
    }

    /**
     * Check is logs deletion is enabled.
     *
     * @return bool
     */
    public function isLogsDeletionEnabled()
    {
        return (bool)$this->getConfigValue('logs_settings/logs_deletion_enabled');
    }

    /**
     * Check is auto logs deletion is enabled.
     *
     * @return bool
     */
    public function isAutoLogsDeletionEnabled()
    {
        return (bool)$this->getConfigValue('logs_settings/auto_logs_deletion_enabled');
    }

    /**
     * Get days to auto clear action logs.
     *
     * @return int
     */
    public function getDaysToClearActionLogs()
    {
        return (int)$this->getConfigValue('logs_settings/clear_actions_logs_after');
    }

    /**
     * Get days to auto clear login logs.
     *
     * @return int
     */
    public function getDaysToClearLoginLogs()
    {
        return (int)$this->getConfigValue('logs_settings/clear_login_logs_after');
    }

    /**
     * Get days to auto clear page view logs.
     *
     * @return int
     */
    public function getDaysToClearPageViewLogs()
    {
        return (int)$this->getConfigValue('logs_settings/clear_page_view_logs_after');
    }
}
