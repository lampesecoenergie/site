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

namespace Ced\Amazon\Model\Order;

use Magento\Framework\Model\AbstractModel;
use Ced\Amazon\Api\Data\Order\ItemInterface;

class Item extends AbstractModel implements ItemInterface
{
    const NAME = 'ced_amazon_order_item';

    public function _construct()
    {
        $this->_init(\Ced\Amazon\Model\ResourceModel\Order\Item::class);
    }

    /**
     * Get Asin
     * @return string
     */
    public function getAsin()
    {
        return $this->getData(self::COLUMN_ASIN);
    }

    /**
     * Get SKU
     * @return string
     */
    public function getSku()
    {
        return $this->getData(self::COLUMN_SKU);
    }

    /**
     * Get Order Item Id
     * @return string
     */
    public function getOrderItemId()
    {
        return $this->getData(self::COLUMN_ORDER_ITEM_ID);
    }

    /**
     * Get QTY Ordered
     * @return string
     */
    public function getQtyOrdered()
    {
        return $this->getData(self::COLUMN_QTY_ORDERED);
    }

    /**
     * Get QTY Shipped
     * @return string
     */
    public function getQtyShipped()
    {
        return $this->getData(self::COLUMN_QTY_SHIPPED);
    }

    /**
     * Get Order Id
     * @return string
     */
    public function getOrderId()
    {
        return $this->getData(self::COLUMN_ORDER_ID);
    }

    /**
     * Get Magento Order Item Id
     * @return string
     */
    public function getMagentoOrderItemId()
    {
        return $this->getData(self::COLUMN_MAGENTO_ORDER_ITEM_ID);
    }

    /**
     * Get Customized Url
     * @return string
     */
    public function getCustomizedUrl()
    {
        return $this->getData(self::COLUMN_CUSTOMIZED_URL);
    }

    /**
     * Get Customized Data
     * @return string
     */
    public function getCustomizedData()
    {
        return $this->getData(self::COLUMN_CUSTOMIZED_DATA);
    }

    /**
     * Get Created At
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::COLUMN_CREATED_AT);
    }

    /**
     * Get Updated Ad
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::COLUMN_UPDATED_AT);
    }
}
