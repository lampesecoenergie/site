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
 * Interface ProfileRepositoryInterface
 * @package Ced\Integrator\Api
 * @api
 */
interface ProfileRepositoryInterface extends \Ced\Integrator\Api\RepositoryInterface
{

    /**
     * Get a Profiles by Product Id
     * @param string $productId
     * @return \Ced\Integrator\Api\Data\ProfileSearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByProductId($productId);

    /**
     * Get profile product ids
     * @param int $id
     * @param array $productIds
     * @return array
     * @throws  \Exception
     */
    public function getAssociatedProductIds($id, $storeId = 0, array $productIds = []);

    /**
     * Get profile products
     * @param int $id
     * @param array $productIds
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getAssociatedProducts($id, $storeId = 0, array $productIds = []);
}
