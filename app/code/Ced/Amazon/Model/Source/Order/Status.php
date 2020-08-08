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

namespace Ced\Amazon\Model\Source\Order;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Status
 * @package Ced\Amazon\Model\Source\Order\Status
 */
class Status extends AbstractSource
{
    // Custom Status
    const NOT_IMPORTED = 'NotImported';
    const IMPORTED = 'Imported';
    const ACKNOWLEDGED = 'Acknowledged';
    const COMPLETED = 'Completed';
    const FAILED = 'Failed';

    // Common Status
    const UNSHIPPED = \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_UNSHIPPED;
    const PARTIALLY_SHIPPED = \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_PARTIALLY_SHIPPED;
    const SHIPPED = \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_SHIPPED;
    const CANCELLED = \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_CANCELED;

    // Api Status
    const PENDING = \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_PENDING;
    const UNFULFILLABLE = \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_UNFULFILLABLE;
    const PENDING_AVAILABILITY = \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_PENDING_AVAILABILITY;

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => self::PENDING,
                'label' => __('Pending'),
            ],
            [
                'value' => self::UNSHIPPED,
                'label' => __('Unshipped'),
            ],
            [
                'value' => self::PARTIALLY_SHIPPED,
                'label' => __('Partially Shipped'),
            ],
            [
                'value' => self::FAILED,
                'label' => __('Failed'),
            ],
            [
                'value' => self::IMPORTED,
                'label' => __('Imported'),
            ],
            [
                'value' => self::NOT_IMPORTED,
                'label' => __('Not Imported'),
            ],
            [
                'value' => self::ACKNOWLEDGED,
                'label' => __('Acknowledged'),
            ],
            [
                'value' => self::SHIPPED,
                'label' => __('Shipped'),
            ],
            [
                'value' => self::COMPLETED,
                'label' => __('Completed'),
            ],
            [
                'value' => self::CANCELLED,
                'label' => __('Cancelled'),
            ],
            [
                'value' => self::UNFULFILLABLE,
                'label' => __('Unfulfillable'),
            ],
            [
                'value' => self::PENDING_AVAILABILITY,
                'label' => __('Pending Availability'),
            ]
        ];
    }
}
