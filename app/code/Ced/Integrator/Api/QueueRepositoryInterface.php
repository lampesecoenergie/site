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

namespace Ced\Integrator\Api;

/**
 * Interface QueueRepositoryInterface
 * @package Ced\Integrator\Api
 * @api
 */
interface QueueRepositoryInterface extends \Ced\Integrator\Api\RepositoryInterface
{
    /**
     * Add item to queue
     * @param \Ced\Integrator\Api\Data\Queue\DataInterface $data
     * @return bool
     * @throws  \Exception
     */
    public function push(\Ced\Integrator\Api\Data\Queue\DataInterface $data);

    /**
     * Get first item with provided type and status
     * @param string $type
     * @param string|array $status
     * @param string|array|null $operation
     * @return \Ced\Amazon\Api\Data\QueueInterface
     */
    public function pop($type, $status, $operation = 'Update');

    /**
     * Set Feed Id to PROCESSED queues
     * @param $ids
     * @param $feedId
     * @return mixed
     */
    public function addFeedId(array $ids = [], $feedId = "0");

    /**
     * Save
     * @param \Ced\Integrator\Api\Data\QueueInterface $item
     * @return int
     */
    public function save(\Ced\Integrator\Api\Data\QueueInterface $item);
}
