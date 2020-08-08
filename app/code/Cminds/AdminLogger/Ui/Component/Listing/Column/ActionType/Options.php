<?php

namespace Cminds\AdminLogger\Ui\Component\Listing\Column\ActionType;

use Magento\Framework\Data\OptionSourceInterface;
use Cminds\AdminLogger\Model\Config as ModuleConfig;

class Options implements OptionSourceInterface
{
    /**
     * Return array of Action Types as value-label pairs.
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => ModuleConfig::ACTION_ADMIN_LOGIN_SUCCESS,
                'label' => 'Admin Login Success',
            ],
            [
                'value' => ModuleConfig::ACTION_ADMIN_LOGIN_FAILED,
                'label' => 'Admin Login Failed',
            ],
            [
                'value' => ModuleConfig::ACTION_ADMIN_PASSWORD_CHANGE_REQUEST,
                'label' => 'Admin Password Change Request',
            ],
            [
                'value' => ModuleConfig::ACTION_PAGE_VIEW,
                'label' => 'Admin Page View',
            ],
            [
                'value' => ModuleConfig::ACTION_CONFIGURATION_UPDATE,
                'label' => 'Configuration Update',
            ],
            [
                'value' => ModuleConfig::ACTION_PRODUCT_CREATE,
                'label' => 'Product Create',
            ],
            [
                'value' => ModuleConfig::ACTION_PRODUCT_UPDATE,
                'label' => 'Product Update',
            ],
            [
                'value' => ModuleConfig::ACTION_PRODUCT_DELETE,
                'label' => 'Product Delete',
            ],
            [
                'value' => ModuleConfig::ACTION_CATEGORY_CREATE,
                'label' => 'Category Create',
            ],
            [
                'value' => ModuleConfig::ACTION_CATEGORY_UPDATE,
                'label' => 'Category Update',
            ],
            [
                'value' => ModuleConfig::ACTION_CATEGORY_DELETE,
                'label' => 'Category Delete',
            ],
            [
                'value' => ModuleConfig::ACTION_CUSTOMER_CREATE,
                'label' => 'Customer Create',
            ],
            [
                'value' => ModuleConfig::ACTION_CUSTOMER_UPDATE,
                'label' => 'Customer Update',
            ],
            [
                'value' => ModuleConfig::ACTION_CUSTOMER_DELETE,
                'label' => 'Customer Delete',
            ],
            [
                'value' => ModuleConfig::ACTION_CONTENT_PAGE_CREATE,
                'label' => 'Content Page Create',
            ],
            [
                'value' => ModuleConfig::ACTION_CONTENT_PAGE_UPDATE,
                'label' => 'Content Page Update',
            ],
            [
                'value' => ModuleConfig::ACTION_CONTENT_PAGE_DELETE,
                'label' => 'Content Page Delete',
            ],
            [
                'value' => ModuleConfig::ACTION_CONTENT_BLOCK_CREATE,
                'label' => 'Content Block Create',
            ],
            [
                'value' => ModuleConfig::ACTION_CONTENT_BLOCK_UPDATE,
                'label' => 'Content Block Update',
            ],
            [
                'value' => ModuleConfig::ACTION_CONTENT_BLOCK_DELETE,
                'label' => 'Content Block Delete',
            ],
            [
                'value' => ModuleConfig::ACTION_CONTENT_WIDGET_CREATE,
                'label' => 'Content Widget Create',
            ],
            [
                'value' => ModuleConfig::ACTION_CONTENT_WIDGET_UPDATE,
                'label' => 'Content Widget Update',
            ],
            [
                'value' => ModuleConfig::ACTION_CONTENT_WIDGET_DELETE,
                'label' => 'Content Widget Delete',
            ],
            [
                'value' => ModuleConfig::ACTION_ORDER_INVOICE_CREATE,
                'label' => 'Order Invoice Create',
            ],
            [
                'value' => ModuleConfig::ACTION_ORDER_CREDITMEMO_CREATE,
                'label' => 'Order Creditmemo Create',
            ],
            [
                'value' => ModuleConfig::ACTION_ORDER_SHIPMENT_CREATE,
                'label' => 'Order Shipment Create',
            ],
            [
                'value' => ModuleConfig::ACTION_ORDER_BILLING_ADDRESS_UPDATE,
                'label' => 'Order Billing Address Update',
            ],
            [
                'value' => ModuleConfig::ACTION_ORDER_SHIPPING_ADDRESS_UPDATE,
                'label' => 'Order Shipping Address Update',
            ],
            [
                'value' => ModuleConfig::ACTION_ORDER_STATUS_UPDATE,
                'label' => 'Order Status Update',
            ],
            [
                'value' => ModuleConfig::ACTION_ORDER_COMMENT_ADD,
                'label' => 'Order Comment Add',
            ],
        ];

        return $options;
    }
}
