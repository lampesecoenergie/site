<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Plugin\Quote;

use Closure;

class QuoteToOrderItem
{
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    ) {
        /** @var $orderItem \Magento\Sales\Model\Order\Item */
        $orderItem = $proceed($item, $additional);
        //result of function 'convert' in class 'Magento\Quote\Model\Quote\Item\ToOrderItem'
        $orderItem->setData("amazon_order_id", $item->getAmazonOrderId());
        $orderItem->setData("amazon_order_item_id", $item->getAmazonOrderItemId());
        // return an object '$orderItem' which will replace result of function 'convert' in class
        // 'Magento\Quote\Model\Quote\Item\ToOrderItem'
        return $orderItem;
    }
}
