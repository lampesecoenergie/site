<?php
/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Magetop_Productslider
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

namespace Magetop\Productslider\Model\Config\Source;

/**
 * Class ProductTypeWidget
 * @package Magetop\Productslider\Model\Config\Source
 */
class ProductTypeWidget extends ProductType
{
    /**
     * @return array
     */
    public function toArray()
    {
        $options = parent::toArray();

        unset($options[self::CATEGORY]);
        unset($options[self::CUSTOM_PRODUCTS]);

        return $options;
    }
}