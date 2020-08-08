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
namespace Ves\Themesettings\Block\Html;

class Header extends \Magento\Framework\View\Element\Template
{
    /**
     * Current template name
     *
     * @var string
     */
    protected $_template = 'header/default.phtml';

    /**
     * @var \Ves\Themesettings\Helper\Theme
     */
    protected $_vesTheme;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context  
     * @param \Ves\Themesettings\Helper\Theme                  $vesTheme 
     * @param array                                            $data     
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ves\Themesettings\Helper\Theme $vesTheme,
        array $data = []
        ) {
        parent::__construct($context, $data);
        $this->_vesTheme = $vesTheme;
        $this->_pageConfig = $context->getPageConfig();

        $ves = $this->_vesTheme;
        $template = $this->_vesTheme->getHeaderCfg("general_settings/header_layout");

        if($ves->getGeneralCfg("general_settings/paneltool")){
            $header_request = $this->getRequest()->getParam('header_layout', $this->getRequest()->getParam('header', false));
            $header_request = trim($header_request);
            if($header_request) {
                $template = $header_request.".phtml";
            }
        }
        $this->_template = "Ves_Themesettings::header/".$template;
        $template = str_replace(".phtml", "", $template);
        $this->_pageConfig->addBodyClass("header-" . $template);
    }

    public function toHtml(){
        

        return parent::toHtml();
    }

    public function getVesElemet($elementName){
        $html = $this->getLayout()->renderElement($elementName);
        return $html;
    }
}