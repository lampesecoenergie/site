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

namespace Ced\Amazon\Model\Order\Import;

use Ced\Amazon\Api\Data\Order\Import\ResultInterface;
use Magento\Framework\DataObject;

class Result extends DataObject implements ResultInterface
{
    /**
     * Set Order Import Params
     * @param string $params
     * @return $this
     */
    public function setParams($params)
    {
        return $this->setData(self::PARAMS, $params);
    }

    /**
     * Get Order Import Params
     * @return string
     */
    public function getParams()
    {
        return $this->getData(self::PARAMS);
    }

    /**
     * Set Order Total
     * @param integer $value
     * @return $this
     */
    public function setOrderTotal($value)
    {
        return $this->setData(self::ORDER_TOTAL, $value);
    }

    /**
     * Get Order Total
     * @return integer
     */
    public function getOrderTotal()
    {
        return $this->getData(self::ORDER_TOTAL);
    }

    /**
     * Set Order Import Total
     * @param $value
     * @return $this
     */
    public function setOrderImportedTotal($value)
    {
        return $this->setData(self::ORDER_IMPORTED_TOTAL, $value);
    }

    /**
     * Get Order Import Total
     * @return integer
     */
    public function getOrderImportedTotal()
    {
        return $this->getData(self::ORDER_IMPORTED_TOTAL);
    }

    /**
     * Add Order Id
     * @param $value
     * @return $this
     */
    public function addId($value)
    {
        if (!empty($value)) {
            $ids = $this->getIds();
            $ids[] = $value;
            $this->setIds($ids);
        }

        return $this;
    }

    /**
     * Set Order Ids
     * @param mixed $value
     * @return $this
     */
    public function setIds($value)
    {
        if (is_array($value)) {
            $this->setData(self::IDS, $value);
        }

        return $this;
    }

    /**
     * Get Order Ids
     * @return mixed
     */
    public function getIds()
    {
        $ids = $this->getData(self::IDS);
        $ids = is_array($ids) ? $ids : [];
        return $ids;
    }
}
