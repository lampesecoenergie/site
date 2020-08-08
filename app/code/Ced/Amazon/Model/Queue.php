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
 * Class Queue
 * @package Ced\Amazon\Model
 */
class Queue extends AbstractModel implements \Ced\Amazon\Api\Data\QueueInterface
{
    const NAME = 'ced_amazon_queue';

    const COLUMN_ID = 'id';

    const COLUMN_TYPE = 'type';

    const COLUMN_OPERATION_TYPE = 'operation_type';

    const COLUMN_STATUS = 'status';

    const COLUMN_DEPENDS = 'depends';

    const COLUMN_SPECIFICS = 'specifics';

    const COLUMN_ACCOUNT_ID = 'account_id';

    const COLUMN_MARKETPLACE = 'marketplace';

    const COLUMN_CREATED_AT = 'created_At';

    const COLUMN_EXECUTED_AT = 'executed_at';

    public function _construct()
    {
        $this->_init(\Ced\Amazon\Model\ResourceModel\Queue::class);
    }

    /**
     * Get Status of item
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::COLUMN_STATUS);
    }

    /**
     * Get Type of item
     * @return string
     */
    public function getType()
    {
        return $this->getData(self::COLUMN_TYPE);
    }

    /**
     * Get OperationType of item
     * @return string
     */
    public function getOperationType()
    {
        return $this->getData(self::COLUMN_OPERATION_TYPE);
    }

    /**
     * Get Marketplace of item
     * @return string
     */
    public function getMarketplace()
    {
        return $this->getData(self::COLUMN_MARKETPLACE);
    }

    /**
     * Get AccountId of item
     * @return string
     */
    public function getAccountId()
    {
        return $this->getData(self::COLUMN_ACCOUNT_ID);
    }

    /**
     * Get Specifics
     * @return array
     */
    public function getSpecifics()
    {
        $specifics = $this->getData(self::COLUMN_SPECIFICS);
        if (!empty($specifics) && !is_array($specifics)) {
            $specifics = json_decode($specifics, true);
        }

        if (!is_array($specifics)) {
            $specifics = [];
        }

        return $specifics;
    }

    /**
     * Set Status
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(self::COLUMN_STATUS, $status);
    }

    /**
     * Set Executed at
     * @param $date
     * @return $this
     */
    public function setExecutedAt($date)
    {
        return $this->setData(self::COLUMN_EXECUTED_AT, $date);
    }
}
