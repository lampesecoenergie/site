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

namespace Ced\Amazon\Model\Source\Feed;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Type
 * @package Ced\Amazon\Model\Source
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
                'value' => \Amazon\Sdk\Api\Feed::PRODUCT,
                'label' => __('Product'),
            ],
            [
                'value' => \Amazon\Sdk\Api\Feed::PRODUCT_INVENTORY,
                'label' => __('Product Inventory'),
            ],
            [
                'value' => \Amazon\Sdk\Api\Feed::PRODUCT_PRICING,
                'label' => __('Product Price'),
            ],
            [
                'value' => \Amazon\Sdk\Api\Feed::PRODUCT_IMAGE,
                'label' => __('Product Image'),
            ],
            [
                'value' => \Amazon\Sdk\Api\Feed::PRODUCT_OVERRIDES,
                'label' => __('Product Overrides'),
            ],
            [
                'value' => \Amazon\Sdk\Api\Feed::PRODUCT_RELATIONSHIP,
                'label' => __('Product Relationship'),
            ],
            [
                'value' => \Amazon\Sdk\Api\Feed::ORDER_ACKNOWLEDGEMENT,
                'label' => __('Order Acknowledgement'),
            ],
            [
                'value' => \Amazon\Sdk\Api\Feed::ORDER_PAYMENT_ADJUSTMENT,
                'label' => __('Order Adjustment'),
            ],
            [
                'value' => \Amazon\Sdk\Api\Feed::ORDER_FULFILLMENT,
                'label' => __('Order Fulfillment'),
            ],
            [
                'value' => \Amazon\Sdk\Api\Feed::MOCK_FEED,
                'label' => __('Mock Feed'),
            ],
        ];
    }
}
