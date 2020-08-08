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

namespace Ced\Integrator\Repository;

class SearchResults extends \Magento\Framework\Api\SearchResults implements
    \Ced\Integrator\Api\Data\SearchResultsInterface
{
    /**
     * @param int $id
     * @param \Ced\Integrator\Api\Data\ProfileInterface $item
     * @return mixed
     */
    public function setItem($id, $item)
    {
        $this->_data[self::KEY_ITEMS][$id] = $item;
        return $this;
    }

    /**
     * @param $id
     * @return \Ced\Integrator\Api\Data\ProfileInterface|null
     */
    public function getItem($id)
    {
        $item = null;
        if (isset($this->_data[self::KEY_ITEMS][$id])) {
            $item = $this->_data[self::KEY_ITEMS][$id];
        }

        return $item;
    }
}
