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

class ImportEntities implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Templates'),
                'value' => 'template',
            ],
            [
                'label' => __('Filters'),
                'value' => 'rule',
            ],
            [
                'label' => __('Dynamic Attributes'),
                'value' => 'dynamic_attribute',
            ],
            [
                'label' => __('Dynamic Categories'),
                'value' => 'dynamic_category',
            ],
            [           
                'label' => __('Dynamic Variables'),
                'value' => 'dynamic_variable',
            ],
        ];
    }
}
