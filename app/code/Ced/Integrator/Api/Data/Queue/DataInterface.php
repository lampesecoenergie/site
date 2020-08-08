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
 * @package     Ced_Integrator
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Api\Data\Queue;

interface DataInterface
{
    /**
     * Set data to add in queue
     * @param array $data
     * @return void
     */
    public function setData(array $data = []);

    /**
     * Get all queue data
     * @return array
     */
    public function getData();

    /**
     * Get Specifics
     * @return array
     */
    public function getSpecifics();

    /**
     * Set Specifics
     * @param array $data
     * @return void
     */
    public function setSpecifics(array $data);

    /**
     * Get Type
     * @return string
     */
    public function getType();

    /**
     * Set Type
     * @param string $type
     * @return string
     */
    public function setType($type);

    /**
     * Get Operation Type
     * @return string
     */
    public function getOperationType();

    /**
     * Set Operation Type
     * @param string $operationType
     * @return void
     */
    public function setOperationType($operationType);

    /**
     * Set ids
     * @param array $ids
     * @return void
     */
    public function setIds(array $ids = []);

    /**
     * Get Ids
     * @return array
     */
    public function getIds();

    /**
     * Get Priorty
     * @return string
     */
    public function getPriorty();

    /**
     * Set Priorty
     * @param $priorty
     * @return void
     */
    public function setPriorty($priorty);

    /**
     * Get Depends
     * @return string
     */
    public function getDepends();

    /**
     * Set Depends
     * @param $depends
     * @return void
     */
    public function setDepends($depends);

    /**
     * Get Marketplace Ids comma-separated
     * @return string
     */
    public function getMarketplace();

    /**
     * Set Marketplace Ids comma-separated
     * @param $marketplace
     * @return void
     */
    public function setMarketplace($marketplace);

    /**
     * Get Account Id
     * @return string
     */
    public function getAccountId();

    /**
     * Set Account Id
     * @param $accountId
     * @return int
     */
    public function setAccountId($accountId);
}
