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
class Sku extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Text
{
    function getCondition()
    {
        if(trim($this->getValue())=='')
            return null;
        $skuIds = explode(',', $this->getValue());
        $skuIdsArray = array();
        foreach($skuIds as $skuId)
            $skuIdsArray[] = trim($skuId);
        if(count($skuIdsArray) == 1)
        {
            $likeExpression = $this->_resourceHelper->addLikeEscape($this->getValue(), array('position' => 'any'));
            return array('like' => $likeExpression);
        }
        else
            return array('inset' => $skuIdsArray);
    }
    
}
