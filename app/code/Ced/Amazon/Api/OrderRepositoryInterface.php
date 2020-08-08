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

namespace Ced\Amazon\Api;

/**
 * Interface OrderRepositoryInterface
 * @package Ced\Amazon\Api
 * @api
 */
interface OrderRepositoryInterface extends \Ced\Integrator\Api\OrderRepositoryInterface
{
    /**
     * Get By Id
     * @param string $id
     * @return \Ced\Amazon\Api\Data\OrderInterface
     */
    public function getById($id);

    /**
     * Get By Magento Order Id
     * @param string $id
     * @return \Ced\Amazon\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByOrderId($id);

    /**
     * Check By Magento Order Id
     * @param $id
     * @return boolean
     */
    public function isMarketplaceOrder($id);

    /**
     * Save
     * @param \Ced\Amazon\Api\Data\OrderInterface $order
     * @return int
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(\Ced\Amazon\Api\Data\OrderInterface $order);
}
