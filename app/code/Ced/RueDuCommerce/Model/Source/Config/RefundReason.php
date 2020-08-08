<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_RueDuCommerce
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RueDuCommerce\Model\Source\Config;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class ServiceUrl
 * @package Ced\RueDuCommerce\Model\Source
 */
class RefundReason extends AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        return array(
            array(
                'value' => "",
                'label' => "--Please Select Reason--",
            ),
            array(
                'value' => "15",
                'label' => "Out of stock",
            ),
            array(
                'value' => "16",
                'label' => "Cancelled order before shipment made",
            ),
            array(
                'value' => "17",
                'label' => "Item returned",
            ),
            array(
                'value' => "18",
                'label' => "Item not received",
            ),
            array(
                'value' => "19",
                'label' => "Agreement found with the vendor",
            )
        );
    }
}
