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
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\Source\Config;

class Price implements \Magento\Framework\Option\ArrayInterface
{
    const TYPE_DEFAULT = 'default';
    const TYPE_FIXED_INCREASE = 'plus_fixed';
    const TYPE_FIXED_DECREASE = 'min_fixed';
    const TYPE_PERCENTAGE_INCREASE = 'plus_per';
    const TYPE_PERCENTAGE_DECREASE = 'min_per';
    const TYPE_ATTRIBUTE = 'differ';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'Default Magento Price',
                'value' => self::TYPE_DEFAULT
            ],
            [
                'label' => 'Increase By Fixed Price',
                'value' => self::TYPE_FIXED_INCREASE
            ],
            [
                'label' => 'Increase By Fixed Percentage',
                'value' => self::TYPE_PERCENTAGE_INCREASE
            ],
            [
                'label' => 'Decrease By Fixed Price',
                'value' => self::TYPE_FIXED_DECREASE
            ],
            [
                'label' => 'Decrease By Fixed Percentage',
                'value' => self::TYPE_PERCENTAGE_DECREASE
            ],
            [
                'label' => 'Set individually for each product',
                'value' => self::TYPE_ATTRIBUTE
            ],
        ];
    }
}
