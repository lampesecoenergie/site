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
 * Class Error
 * @package Ced\Amazon\Model\Source\Product
 */
class Error extends AbstractSource
{
    // Custom Status
    const VALID = 'valid';
    const INVALID = 'Inactive';
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
                'value' => self::VALID,
                'label' => __('Valid'),
            ],
            [
                'value' => self::INVALID,
                'label' => __('Invalid'),
            ],
        ];
    }
}
