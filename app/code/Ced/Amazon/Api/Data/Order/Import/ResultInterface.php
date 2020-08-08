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
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Api\Data\Order\Import;

interface ResultInterface
{
    const IDS = "ids";
    const PARAMS = "params";
    const ORDER_TOTAL = "order_total";
    const ORDER_IMPORTED_TOTAL = "order_imported_total";

    /**
     * Set Order Import Params
     * @param ParamsInterface $params
     * @return $this
     */
    public function setParams($params);

    /**
     * Get Order Import Params
     * @return ParamsInterface
     */
    public function getParams();

    /**
     * Set Order Total
     * @param integer $value
     * @return $this
     */
    public function setOrderTotal($value);

    /**
     * Get Order Total
     * @return integer
     */
    public function getOrderTotal();

    /**
     * Set Order Import Total
     * @param $value
     * @return $this
     */
    public function setOrderImportedTotal($value);

    /**
     * Get Order Import Total
     * @return integer
     */
    public function getOrderImportedTotal();

    /**
     * Add Order Id
     * @param $value
     * @return $this
     */
    public function addId($value);

    /**
     * Set Order Ids
     * @param mixed $value
     * @return $this
     */
    public function setIds($value);

    /**
     * Get Order Ids
     * @return mixed
     */
    public function getIds();
}
