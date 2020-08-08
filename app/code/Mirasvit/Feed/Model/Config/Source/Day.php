<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Day implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Sunday'),
                'value' => '0',
            ],
            [
                'label' => __('Monday'),
                'value' => '1',
            ],
            [
                'label' => __('Tuesday'),
                'value' => '2',
            ],
            [
                'label' => __('Wednesday'),
                'value' => '3',
            ],
            [
                'label' => __('Thursday'),
                'value' => '4',
            ],
            [
                'label' => __('Friday'),
                'value' => '5',
            ],
            [
                'label' => __('Saturday'),
                'value' => '6',
            ],
        ];
    }
}
