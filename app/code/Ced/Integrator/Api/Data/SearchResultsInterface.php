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

namespace Ced\Integrator\Api\Data;

/**
 * Interface SearchResultsInterface
 * @package Ced\Integrator\Api\Data
 * @api
 */
interface SearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * @return \Ced\Integrator\Api\Data\DataInterface[]
     */
    public function getItems();

    /**
     * @param \Ced\Integrator\Api\Data\DataInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * @param $id
     * @param \Ced\Integrator\Api\Data\DataInterface $item
     * @return mixed
     */
    public function setItem($id, $item);

    /**
     * @param $id
     * @return \Ced\Integrator\Api\Data\DataInterface|null
     */
    public function getItem($id);
}
