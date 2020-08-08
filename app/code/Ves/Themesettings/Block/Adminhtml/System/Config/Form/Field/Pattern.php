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

class Pattern extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * Retrieve element HTML markup
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {   
        //$html = $element->getElementHtml();
        $html = '';
        //$moduleName = $this->getModuleName();
        //$patternUrl = $this->getViewFileUrl($moduleName.'/images/pattern');

        $patternUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'ves/themesettings/patterns';

        $options = $element->getValues();
        $html .= '<div class="radio_images" id="' . $element->getHtmlId() . '">';
        foreach ($options as $_option) {
            $ranId = rand().time();
            $checked = ($element->getEscapedValue() == $_option['value']) ? ' checked' : '';
            $html .= '<label style="display: inline-block;" title="' . $_option['label'] . '">';
            $html .= '<input type="radio" name="' . $element->getName() . '" value="' . $_option['value'] . '" ' . $checked . ' />';
            $html .= '<img id="pattern-'.$element->getHtmlId().'-'.$ranId.'" src="'.$patternUrl . '/' . $_option['value'].'.png" alt="'.$_option['label'].'"/>';
            $html .= '</label>';
        }
        $html .= '</div>';
        $html .= $element->getAfterElementHtml(); 
        return $html;
    }
}