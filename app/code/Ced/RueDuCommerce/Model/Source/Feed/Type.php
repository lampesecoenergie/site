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

namespace Ced\RueDuCommerce\Model\Source\Feed;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Type
 *
 * @package Ced\RueDuCommerce\Model\Source
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
                'value' => \RueDuCommerceSdk\Core\Request::FEED_CODE_INVENTORY_UPDATE,
                'label' => __(\RueDuCommerceSdk\Core\Request::FEED_CODE_INVENTORY_UPDATE),
            ],
            [
                'value' => \RueDuCommerceSdk\Core\Request::FEED_CODE_ITEM_UPDATE,
                'label' => __(\RueDuCommerceSdk\Core\Request::FEED_CODE_ITEM_UPDATE),
            ],
            [
                'value' => \RueDuCommerceSdk\Core\Request::FEED_CODE_ITEM_DELETE,
                'label' => __(\RueDuCommerceSdk\Core\Request::FEED_CODE_ITEM_DELETE),
            ],
            [
                'value' => \RueDuCommerceSdk\Core\Request::FEED_CODE_ORDER_SHIPMENT,
                'label' => __(\RueDuCommerceSdk\Core\Request::FEED_CODE_ORDER_SHIPMENT),
            ],
            [
                'value' => \RueDuCommerceSdk\Core\Request::FEED_CODE_PRICE_UPDATE,
                'label' => __(\RueDuCommerceSdk\Core\Request::FEED_CODE_PRICE_UPDATE),
            ],
            [
                'value' => \RueDuCommerceSdk\Core\Request::FEED_CANCEL_ORDER_ITEM,
                'label' => __(\RueDuCommerceSdk\Core\Request::FEED_CANCEL_ORDER_ITEM),
            ],
            [
                'value' => \RueDuCommerceSdk\Core\Request::FEED_CODE_ORDER_CREATE,
                'label' => __(\RueDuCommerceSdk\Core\Request::FEED_CODE_ORDER_CREATE),
            ]
        ];
    }
}
