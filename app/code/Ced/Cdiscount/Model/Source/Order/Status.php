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
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Model\Source\Order;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Status
 *
 * @package Ced\Cdiscount\Model\Source\Order\Status
 */
class Status extends AbstractSource
{
    const IMPORTED = 'imported';
    const ACKNOWLEDGED = 'acknowledged';
    const PARTIALLY_SHIPPED = 'partially_shipped';
    const SHIPPED = 'shipped';
    const COMPLETED = 'completed';
    const CANCELLED = 'cancelled';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => self::IMPORTED,
                'label' => __('Imported'),
            ],
            [
                'value' => self::ACKNOWLEDGED,
                'label' => __('Acknowledged'),
            ],
            [
                'value' => self::PARTIALLY_SHIPPED,
                'label' => __('Partially Shipped'),
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
            ]
        ];
    }
}
