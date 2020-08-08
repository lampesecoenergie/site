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
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\Source\Order\Failure;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Reason
 * @package Ced\Amazon\Model\Source\Order\Failure\Reason
 */
class Reason extends AbstractSource
{
    const NO_ERROR = "NO_ERROR";
    const ERROR_OUT_OF_STOCK_CODE = "E101";
    const ERROR_NOT_ENABLED_CODE = "E102";
    const ERROR_DOES_NOT_EXISTS_CODE = "E103";
    const ERROR_ITEM_DATA_NOT_AVAILABLE_CODE = "E104";
    const ERROR_CUSTOMER_CREATE_FAILURE_CODE = 'E105';
    const ERROR_ORDER_IMPORT_EXCEPTION_CODE = 'E500';

    const ERROR_MESSAGE_OUT_OF_STOCK = "'%s' SKU out of stock";
    const ERROR_MESSAGE_NOT_ENABLED = "'%s' SKU not enabled on store '%s'";
    const ERROR_MESSAGE_DOES_NOT_EXISTS = "'%s' SKU not exists on store '%s'";
    const ERROR_MESSAGE_ITEM_DATA_NOT_AVAILABLE =
        "'%s' SKU not available in order items or order item is cancelled. [qty: %s]";
    const ERROR_MESSAGE_CUSTOMER_CREATE_FAILURE_MESSAGE =
        'Unable to assign the customer. Customer is not available or cannot be created.';
    const ERROR_MESSAGE_ORDER_IMPORT_EXCEPTION = 'Exception occurred during order import. Kindly contact support.';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => self::NO_ERROR,
                'label' => __('No Error'),
            ],
            [
                'value' => self::ERROR_OUT_OF_STOCK_CODE,
                'label' => __('E101: SKU out of stock'),
            ],
            [
                'value' => self::ERROR_NOT_ENABLED_CODE,
                'label' => __('E102: SKU not enabled on store'),
            ],
            [
                'value' => self::ERROR_DOES_NOT_EXISTS_CODE,
                'label' => __('E103: SKU not exists on store'),
            ],
            [
                'value' => self::ERROR_ITEM_DATA_NOT_AVAILABLE_CODE,
                'label' => __('E104: SKU is not available'),
            ],
            [
                'value' => self::ERROR_CUSTOMER_CREATE_FAILURE_CODE,
                'label' => __('E105: Customer is not available'),
            ],
            [
                'value' => self::ERROR_ORDER_IMPORT_EXCEPTION_CODE,
                'label' => __('E500: Unexpected exception occurred'),
            ],
        ];
    }
}
