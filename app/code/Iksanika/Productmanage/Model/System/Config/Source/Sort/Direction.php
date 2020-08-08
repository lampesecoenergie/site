<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace Iksanika\Productmanage\Model\System\Config\Source\Sort;

class Direction implements \Magento\Framework\Option\ArrayInterface
{
    
    const DESC_OPTION   =   'desc';
    const ASC_OPTION    =   'asc';
    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $sortingOptions = array(
            array(
                'value' => \Iksanika\Productmanage\Model\System\Config\Source\Sort\Direction::DESC_OPTION,   
                'label' => __('Descending')
            ),
            array(
                'value' => \Iksanika\Productmanage\Model\System\Config\Source\Sort\Direction::ASC_OPTION,   
                'label' => __('Ascending')
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
