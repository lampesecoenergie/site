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

namespace Ced\Amazon\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Barcode
 * @package Ced\Amazon\Model\Source
 */
class Barcode extends AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [

            [
                'value' => 'UPC',
                'label' => __('UPC'),
            ],
            [
                'value' => 'EAN',
                'label' => __('EAN'),
            ],
            [
                'value' => 'ASIN',
                'label' => __('ASIN'),
            ],
            [
                'value' => 'ISBN',
                'label' => __('ISBN'),
            ],
            [
                'value' => 'GCID',
                'label' => __('GCID'),
            ],
            [
                'value' => 'GTIN',
                'label' => __('GTIN'),
            ],
            [
                'value' => 'PZN',
                'label' => __('PZN')
            ]
        ];
    }
}
