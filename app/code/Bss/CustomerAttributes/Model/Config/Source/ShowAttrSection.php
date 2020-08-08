<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Model\Config\Source;

/**
 * Class ShowAttrSection
 *
 * @package Bss\CustomerAttributes\Model\Config\Source
 */
class ShowAttrSection
{
    /**
     * Return array of options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'customer_attr_section', 'label' => __('Customer Attribute Section')],
            ['value' => 'signin_infor_section', 'label' => __('Sign-in Information Section')],
            ['value' => 'personal_infor_section', 'label' => __('Personal Information')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'customer_attr_section' => __('Customer Attribute Section'),
            'signin_infor_section' => __('Sign-in Information Section'),
            'personal_infor_section' => __('Personal Information')
        ];
    }
}
