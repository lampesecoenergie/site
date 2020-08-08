<?php

namespace BoostMyShop\AdminLogger\Block\Log\Renderer;


class ObjectType extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(\Magento\Framework\DataObject $row)
    {
        return $row->getObjectLabel();
    }

}