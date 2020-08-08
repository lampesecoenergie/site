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
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\Queue;

/**
 * Class Data
 * @package Ced\Amazon\Model\Queue
 */
class Data implements \Ced\Amazon\Api\Data\Queue\DataInterface
{
    /** @var array */
    public $specifics = [];

    /** @var string */
    public $type = \Amazon\Sdk\Api\Feed::PRODUCT;

    /** @var null */
    public $depends = null;

    /** @var string */
    public $priorty = \Ced\Amazon\Model\Source\Queue\Priorty::MEDIUM;

    /** @var string */
    public $operation_type = \Amazon\Sdk\Base::OPERATION_TYPE_UPDATE;

    /** @var string|null */
    public $account_id = null;

    /** @var string|null  */
    public $marketplace = null;

    public function getSpecifics()
    {
        $data = isset($this->specifics) && is_array($this->specifics) ? $this->specifics : [];
        return $data;
    }

    public function setSpecifics(array $specifics = [])
    {
        $this->specifics = $specifics;
    }

    public function setIds(array $ids = [])
    {
        $this->specifics['ids'] = $ids;
    }

    public function getIds()
    {
        $ids = isset($this->specifics['ids']) ? $this->specifics['ids'] : [];
        return $ids;
    }

    public function setData(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function getData()
    {
        $data = [
            'data' => json_encode($this->getData()),
            'type' => $this->getType(),
            'depends' => $this->getDepends(),
            'priorty' => $this->getPriorty(),
            'operation_type' => $this->getOperationType(),
        ];

        return $data;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getDepends()
    {
        return $this->depends;
    }

    public function setDepends($depends)
    {
        $this->depends = $depends;
    }

    public function getPriorty()
    {
        return $this->priorty;
    }

    public function setPriorty($priorty)
    {
        $this->priorty = $priorty;
    }

    public function getOperationType()
    {
        return $this->operation_type;
    }

    public function setOperationType($operationType)
    {
        $this->operation_type = $operationType;
    }

    /**
     * Get Marketplace Ids comma-separated
     * @return string
     */
    public function getMarketplace()
    {
        return $this->marketplace;
    }

    /**
     * Set Marketplace Ids comma-separated
     * @param $marketplace
     * @return void
     */
    public function setMarketplace($marketplace)
    {
        $this->marketplace = $marketplace;
    }

    /**
     * Get Account Id
     * @return string
     */
    public function getAccountId()
    {
        return $this->account_id;
    }

    /**
     * Set Account Id
     * @param $accountId
     * @return int
     */
    public function setAccountId($accountId)
    {
        $this->account_id = $accountId;
    }
}
