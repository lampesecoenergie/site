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
 * @package   Ced_m2.2.EE
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Model\Source\Cdiscount;

class Attributes extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $data = [
            [
                'label' => 'Price',
                'value' => 'Price'
            ]
        ];
        $arrayMerge = array_merge(
            \Ced\Cdiscount\Helper\Category::REQUIRED_ATTRIBUTES,
            \Ced\Cdiscount\Helper\Category::OPTIONAL_ATTRIBUTES
        );

        foreach ($arrayMerge as $key => $value) {
            if ($value == \Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN
            || $value == \Ced\Cdiscount\Helper\Category::ATTRIBUTE_SIZE
            || $value == \Ced\Cdiscount\Helper\Category::ATTRIBUTE_SELLER_PRODUCT_ID
            || in_array($value, \Ced\Cdiscount\Helper\Category::OPTIONAL_ATTRIBUTES)) {
                unset($arrayMerge[$key]);
                continue;
            }
            $data[] = [
                'label' => $value,
                'value' => $value
            ];
        }
        return $data;
    }
}
