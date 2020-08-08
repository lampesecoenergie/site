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
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\Source\Product;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Status
 * @package Ced\Amazon\Model\Source\Product
 */
class Status extends AbstractSource
{
    // Custom Status
    const ACTIVE = 'Active';
    const INACTIVE = 'Inactive';
    const INCOMPLETE = 'Incomplete';
    const NA = 'NotAvailable';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => self::NA,
                'label' => __('Not Available'),
            ],
            [
                'value' => self::ACTIVE,
                'label' => __('Active'),
            ],
            [
                'value' => self::INACTIVE,
                'label' => __('Inactive'),
            ],
            [
                'value' => self::INCOMPLETE,
                'label' => __('Incomplete'),
            ],
        ];
    }
}
