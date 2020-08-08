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

class Image extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * Add Media Chooser
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return String
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {

        $elementId = $element->getHtmlId();
        $html = $element->getElementHtml();

        $html .= "<div id='ves-".$elementId."'>Click Me</div>";
        $html .= "<script type='text/javscript'>
        define(['jquery','mage/adminhtml/browser'
        ], function($) {
            jQuery('#ves-".$elementId."').click(function(){
                alert('abc');
            });
        };});
        </script>";
        return $html;
    }
}
