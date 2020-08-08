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
 * @package   Ced_RueDuCommerce
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RueDuCommerce\Model\Source\Order;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Status
 *
 * @package Ced\RueDuCommerce\Model\Source\Order\Status
 */
class Status extends AbstractSource
{
    const STAGING = 'STAGING';
    const WAITING_ACCEPTANCE = 'WAITING_ACCEPTANCE';
    const WAITING_DEBIT = 'WAITING_DEBIT';
    const WAITING_DEBIT_PAYMENT = 'WAITING_DEBIT_PAYMENT';
    const SHIPPING = 'SHIPPING';
    const SHIPPED = 'shipped';
    const COMPLETED = 'COMPLETED';
    const CANCELED = 'CANCELED';
    const TO_COLLECT = 'TO_COLLECT';
    const RECEIVED = 'RECEIVED';
    const CLOSED = 'CLOSED';
    const REFUSED = 'REFUSED';
    const INCIDENT_OPEN = 'INCIDENT_OPEN';
    const REFUNDED = 'REFUNDED';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => 'new',
                'label' => __('NEW')
            ],
            [
                'value' => 'completed',
                'label' => __('COMPLETED')
            ],
            [
                'value' => 'shipped',
                'label' => __('SHIPPED')
            ]
        ];
        /*return [
            [
                'value' => self::STAGING,
                'label' => __('STAGING'),
            ],
            [
                'value' => self::WAITING_ACCEPTANCE,
                'label' => __('WAITING_ACCEPTANCE'),
            ],
            [
                'value' => self::WAITING_DEBIT,
                'label' => __('WAITING_DEBIT'),
            ],
            [
                'value' => self::WAITING_DEBIT_PAYMENT,
                'label' => __('WAITING_DEBIT_PAYMENT'),
            ],
            [
                'value' => self::SHIPPING,
                'label' => __('SHIPPING'),
            ],
            [
                'value' => self::SHIPPED,
                'label' => __('SHIPPED'),
            ],
            [
                'value' => self::TO_COLLECT,
                'label' => __('TO_COLLECT'),
            ],
            [
                'value' => self::RECEIVED,
                'label' => __('RECEIVED'),
            ],
            [
                'value' => self::CLOSED,
                'label' => __('CLOSED'),
            ],
            [
                'value' => self::REFUSED,
                'label' => __('REFUSED'),
            ],
            [
                'value' => self::CANCELED,
                'label' => __('CANCELED'),
            ],
            [
                'value' => self::INCIDENT_OPEN,
                'label' => __('INCIDENT_OPEN'),
            ],
            [
                'value' => self::REFUNDED,
                'label' => __('REFUNDED'),
            ]
        ];*/
    }
}
