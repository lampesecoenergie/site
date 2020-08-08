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

namespace Ced\Amazon\Model\Source\Order\Config;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class IncrementId
 * @package Ced\Amazon\Model\Source\Order\Config\IncrementId
 */
class IncrementId extends AbstractSource
{
    const ADD_MARKETPLACE_CODE = 'marketplace_code';
    const ADD_AMAZON_ORDER_ID = 'amazon_order_id';
    const ADD_PREFIX = 'prefix';
    const ADD_FULFILLMENT_CHANNEL = 'fulfillment_channel';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => self::ADD_MARKETPLACE_CODE,
                'label' => __('Add Marketplace Code'),
            ],
            [
                'value' => self::ADD_AMAZON_ORDER_ID,
                'label' => __('Add Amazon Order Id'),
            ],
            [
                'value' => self::ADD_PREFIX,
                'label' => __('Add Prefix'),
            ],
            [
                'value' => self::ADD_FULFILLMENT_CHANNEL,
                'label' => __('Add Fulfillment Channel'),
            ]
        ];
    }
}
