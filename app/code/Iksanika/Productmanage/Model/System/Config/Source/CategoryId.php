<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace Iksanika\Productmanage\Model\System\Config\Source;

class CategoryId implements \Magento\Framework\Option\ArrayInterface
{
    
    const CATEGORY_ID   =   0;
    const CATEGORY_NAME =   1;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $sortingOptions = array(
            array(
                'value' => \Iksanika\Productmanage\Model\System\Config\Source\CategoryId::CATEGORY_ID,   
                'label' => __('Category IDs')
            ),
            array(
                'value' => \Iksanika\Productmanage\Model\System\Config\Source\CategoryId::CATEGORY_NAME,   
                'label' => __('Category Names')
            )
        );
        return $sortingOptions;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $returnArray = [];
        foreach($this->toOptionArray() as $item)
        {
            $returnArray[$item['value']] = $item['label'];
        }
        return $returnArray;
    }
}
