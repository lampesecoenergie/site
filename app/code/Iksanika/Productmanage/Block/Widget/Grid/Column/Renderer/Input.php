<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Grid input column renderer
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer;

class Input extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{
    /**
     * @var array
     */
    protected $_values;

    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '<input type="text" ';
        $html .= 'name="' . $this->getColumn()->getId() . '" ';
        $html .= 'value="' . htmlspecialchars($row->getData($this->getColumn()->getIndex())) . '"';
        $html .= 'class="input-text admin__control-text ' . $this->getColumn()->getInlineCss() . '"/>';
        return $html;
    }
}
