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

namespace Ced\Amazon\Plugin\Order;

use Ced\Amazon\Registry\Order;

class AllowedCountries
{
    public $amazonOrderRegistry;

    public function __construct(
        Order $order
    ) {
        $this->amazonOrderRegistry = $order;
    }

    public function afterGetAllowedCountries(
        \Magento\Directory\Model\AllowedCountries $subject,
        $result
    ) {
        // Injecting the Amazon Order Country
        if (isset($this->amazonOrderRegistry) && $country = $this->amazonOrderRegistry->getCountryCode()) {
            $result[$country] = $country;
        }

        return $result;
    }
}
