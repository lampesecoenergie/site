<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Themesettings
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Themesettings\Block\Adminhtml\System\Config\Form\Field;

class CustomAttributes extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
       $this->addColumn('code', [
            'label' => __('Code'),
            'style' => 'width:120px'
            ]);

        $this->addColumn('name', [
            'label' => __('Title'),
            'style' => 'width:120px'
            ]);

        $this->addColumn('category_ids', [
            'label' => __('Category IDs'),
            'style' => 'width:100px'
            ]);

        $this->addColumn('product_skus', [
            'label' => __('Product Skus'),
            'style' => 'width:100px'
            ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Retrieve HTML markup for given form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {

        $isCheckboxRequired = $this->_isInheritCheckboxRequired($element);

        // Disable element if value is inherited from other scope. Flag has to be set before the value is rendered.
        if ($element->getInherit() == 1 && $isCheckboxRequired) {
            $element->setDisabled(true);
        }

        $html = '<td class="label"><label for="' .
            $element->getHtmlId() .
            '">' .
            $element->getLabel() .
            '</label></td>';
        $value = $element->getValue();
        if(isset($value['__empty'])){
            unset($value['__empty']);
            $element->setValue($value);
        }
        $html .= $this->_renderValue($element);

        if ($isCheckboxRequired) {
            $html .= $this->_renderInheritCheckbox($element);
        }

        $html .= '<span' .$this->_renderScopeLabel($element) . '></span>';
        $html .= $this->_renderHint($element);

        return $this->_decorateRowHtml($element, $html);
    }
}