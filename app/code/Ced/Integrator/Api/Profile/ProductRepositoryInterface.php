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

namespace Ced\Integrator\Api\Profile;

/**
 * Interface ProductRepositoryInterface
 * @package Ced\Integrator\Api\Profile
 * @api
 */
interface ProductRepositoryInterface extends \Ced\Integrator\Api\RepositoryInterface
{
    /**
     * Get All Product Ids for given Profile Id as Array
     * @param $profileId
     * @return mixed
     */
    public function getProductIdsByProfileId($profileId);

    /**
     * Get All Product for given Profile Id as a Collection
     * @param $profileId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductsByProfileId($profileId);

    /**
     * Delete product relations by ids
     * @param array $productIds
     * @param int $profileId
     * @return boolean|int
     */
    public function deleteByProductIdsAndProfileId(array $productIds, $profileId);

    /**
     * @param array $productIds
     * @param $profileId
     * @return mixed
     */
    public function addProductsIdsWithProfileId(array $productIds, $profileId);
}
