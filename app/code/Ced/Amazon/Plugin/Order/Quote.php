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

class Quote
{
    public $logger;

    public function __construct(
        \Ced\Amazon\Helper\Logger $logger
    ) {
        $this->logger = $logger;
    }

    public function afterGetItemByProduct(
        \Magento\Quote\Api\Data\CartInterface $subject,
        $result
    ) {
        if ($subject->getAmazonOrderId() && $result) {
            return false;
        }
        return $result;
    }
}
