<?php

namespace Ced\Amazon\Api\Data\Order;

interface ItemInterface
{
    const COLUMN_ID = 'id';
    const COLUMN_ASIN = 'asin';
    const COLUMN_SKU = 'sku';
    const COLUMN_ORDER_ITEM_ID = 'order_item_id';
    const COLUMN_QTY_ORDERED = 'qty_ordered';
    const COLUMN_QTY_SHIPPED = 'qty_shipped';
    const COLUMN_ORDER_ID = 'order_id';
    const COLUMN_MAGENTO_ORDER_ITEM_ID = 'magento_order_item_id';
    const COLUMN_CUSTOMIZED_URL = 'customized_url';
    const COLUMN_CUSTOMIZED_DATA = 'customized_data';
    const COLUMN_CREATED_AT = 'created_at';
    const COLUMN_UPDATED_AT = 'updated_at';

    /**
     * Get Asin
     * @return string
     */
    public function getAsin();

    /**
     * Get SKU
     * @return string
     */
    public function getSku();

    /**
     * Get Order Item Id
     * @return string
     */
    public function getOrderItemId();

    /**
     * Get QTY Ordered
     * @return string
     */
    public function getQtyOrdered();

    /**
     * Get QTY Shipped
     * @return string
     */
    public function getQtyShipped();

    /**
     * Get Order Id
     * @return string
     */
    public function getOrderId();

    /**
     * Get Magento Order Item Id
     * @return string
     */
    public function getMagentoOrderItemId();

    /**
     * Get Customized Url
     * @return string
     */
    public function getCustomizedUrl();

    /**
     * Get Customized Data
     * @return string
     */
    public function getCustomizedData();

    /**
     * Get Created At
     * @return string
     */
    public function getCreatedAt();

    /**
     * Get Updated Ad
     * @return string
     */
    public function getUpdatedAt();
}
