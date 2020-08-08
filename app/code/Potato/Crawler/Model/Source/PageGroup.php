<?php

namespace Potato\Crawler\Model\Source;

use \Magento\Framework\Option\ArrayInterface;

/**
 * Class PageGroup
 */
class PageGroup implements ArrayInterface
{
    const CMS_VALUE      = 1;
    const CATEGORY_VALUE = 2;
    const PRODUCT_VALUE  = 3;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::CMS_VALUE, 'label' => __("Cms")],
            ['value' => self::CATEGORY_VALUE, 'label' => __("Category")],
            ['value' => self::PRODUCT_VALUE, 'label' => __("Product")]
        ];
    }
}