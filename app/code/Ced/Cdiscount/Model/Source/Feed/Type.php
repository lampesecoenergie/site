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

namespace Ced\Cdiscount\Model\Source\Feed;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Type
 *
 * @package Ced\Cdiscount\Model\Source
 */
class Type extends AbstractSource
{

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => 'product_creation',//Cdiscount\Sdk\Api::ACTION_POST_PRODUCT,
                'label' => __('Product Creation')//__(\Cdiscount\Sdk\Api::ACTION_POST_PRODUCT),
            ],
            [
                'value' => 'order_shipment',
                'label' => __('Order Shipment')
            ],
            [
                'value' => 'order_cancellation',
                'label' => __('Order Cancellation')
            ],
            [
                'value' => 'offer_creation',
                'label' => __('Offer Creation')
            ]

        ];
    }
}
