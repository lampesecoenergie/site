<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iksanika\Productmanage\Block\Widget\Grid\Column\Filter;

/**
 * Text grid column filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Category extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Text
{
    function getCondition()
    {
        if(trim($this->getValue())=='')
            return null;
        $categoryIds         =   explode(',', $this->getValue());
        $categoryIdsArray    =   array();
        foreach($categoryIds as $skuId)
            $categoryIdsArray[] = trim($skuId);
        return array('inset' => $categoryIdsArray);
    }
    
}
