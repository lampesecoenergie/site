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

namespace Ced\Amazon\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Order
 * @package Ced\Amazon\Model
 */
class Order extends AbstractModel implements \Ced\Amazon\Api\Data\OrderInterface
{
    const NAME = 'ced_amazon_order';
    const COLUMN_ID = 'id';
    const COLUMN_PO_ID = 'amazon_order_id';
    const COLUMN_PO_DATE = 'purchase_date';
    const COLUMN_MAGENTO_ORDER_ID = 'magento_order_id';
    const COLUMN_MAGENTO_INCREMENT_ID = 'magento_increment_id';
    const COLUMN_STATUS = 'status';
    const COLUMN_ORDER_DATA = 'order_data';
    const COLUMN_ORDER_ITEMS = 'order_items';
    const COLUMN_SHIPMENT_DATA = 'fulfillments';
    const COLUMN_ADJUSTMENT_DATA = 'adjustments';
    const COLUMN_MARKETPLACE_ID = 'marketplace_id';
    const COLUMN_ACCOUNT_ID = 'account_id';
    const COLUMN_FAILURE_REASON = 'reason';
    const COLUMN_FULFILLMENT_CHANNEL = 'fulfillment_channel';
    const COLUMN_SALES_CHANNEL = 'sales_channel';
    const COLUMN_LAST_UPDATE_DATE = 'last_update_date';
    const COLUMN_CREATED_AT = 'created_at';
    const COLUMN_UPDATED_AT = 'updated_at';

    public function _construct()
    {
        $this->_init(\Ced\Amazon\Model\ResourceModel\Order::class);
    }

    /**
     * Get Order By Marketplace Order Id i.e PO Id
     * @param string $poId
     * @return Order|null
     */
    public function getByPurchaseOrderId($poId)
    {
        $order = $this->load($poId, self::COLUMN_PO_ID);
        if ($order->getId() !== null) {
            return $order;
        }

        return null;
    }

    public function loadByMagentoOrderId($magentoOrderId)
    {
        $this->load($magentoOrderId, self::COLUMN_MAGENTO_ORDER_ID);
    }

    /**
     * Get Amazon Order Id
     * @return string
     */
    public function getAmazonOrderId()
    {
        return $this->getData(self::COLUMN_PO_ID);
    }

    /**
     * Get Amazon Order Place Date
     * @return string
     */
    public function getOrderPlaceDate()
    {
        return $this->getData(self::COLUMN_PO_DATE);
    }

    /**
     * Get Amazon Order Status
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::COLUMN_STATUS);
    }

    /**
     * Get Amazon Order Fulfillments
     * @return string
     */
    public function getFulfillments()
    {
        return $this->getData(self::COLUMN_SHIPMENT_DATA);
    }

    /**
     * Get Amazon Order Adjustments
     * @return string
     */
    public function getAdjustments()
    {
        return $this->getData(self::COLUMN_ADJUSTMENT_DATA);
    }

    /**
     * Get Amazon Order Data
     * @return string
     */
    public function getOrderData()
    {
        return $this->getData(self::COLUMN_ORDER_DATA);
    }

    /**
     * Get Amazon Order Items
     * @return string
     */
    public function getOrderItems()
    {
        return $this->getData(self::COLUMN_ORDER_ITEMS);
    }

    /**
     * Get Magento Increment Id
     * @return string
     */
    public function geMagentoIncrementId()
    {
        return $this->getData(self::COLUMN_MAGENTO_INCREMENT_ID);
    }

    /**
     * Get Account Id
     * @return int
     */
    public function getAccountId()
    {
        return $this->getData(self::COLUMN_ACCOUNT_ID);
    }
}
