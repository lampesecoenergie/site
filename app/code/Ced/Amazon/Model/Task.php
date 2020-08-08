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

namespace Ced\Amazon\Model;

class Task
{
    public $data = [];
    public $type = \Amazon\Sdk\Api\Feed::PRODUCT;
    public $depends = null;
    public $priorty = \Ced\Amazon\Model\Source\Queue\Priorty::MEDIUM;
    public $operationType = \Amazon\Sdk\Base::OPERATION_TYPE_UPDATE;

    public function setIds($ids = [])
    {
        $this->data['ids'] = $ids;
    }

    public function getIds()
    {
        $ids = isset($this->data['ids']) ? $this->data['ids'] : [];
        return $ids;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setPriorty($priorty)
    {
        $this->priorty = $priorty;
    }

    public function getPriorty()
    {
        return $this->priorty;
    }

    public function setOperationType($operationType)
    {
        $this->operationType = $operationType;
    }

    public function getOperationType()
    {
        return $this->operationType;
    }

    public function setDepends($depends)
    {
        $this->depends = $depends;
    }

    public function getDepends()
    {
        return $this->depends;
    }
}
