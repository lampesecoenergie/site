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

class Popup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Ves\Themesettings\Helper\Theme
     */
	public $_ves;
    
    /**
     * @var \Ves\Themesettings\Helper\Data
     */
	public $_vesData;

    public $_vesBlockData;
    protected $_blockModel;
	
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context 
     * @param \Ves\Themesettings\Helper\Theme                  $ves     
     * @param \Ves\Themesettings\Helper\Data                   $vesData 
     * @param array                                            $data    
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ves\Themesettings\Helper\Theme $ves,
        \Ves\Themesettings\Helper\Data $vesData,
        \Magento\Cms\Model\Block $blockModel,
        array $data = []
        ) {
        parent::__construct($context, $data);
        $this->_ves = $ves;
        $this->_vesData = $vesData;
        $this->_vesBlockData = $data;
        $this->_blockModel = $blockModel;
    }

    /**
     * Retrieve form action url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('newsletter/subscriber/new', ['_secure' => true]);
    }

	/**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {	
        if(!$this->_ves->getGeneralCfg("promotion_settings/enable_popup")){
            return ;
        }

        $popup_id = $this->_ves->getGeneralCfg("promotion_settings/popup_id");
        $padding = $this->_ves->getGeneralCfg("promotion_settings/padding");
        $prefix_class = $this->_ves->getGeneralCfg("promotion_settings/prefix_class");
        $cms_id = $this->_ves->getGeneralCfg("promotion_settings/cms_id");
        $promotion_content = "";
        if($cms_id) {
            $promotion_content = $this->_blockModel->load($cms_id)->getContent();
            $promotion_content = $this->_vesData->filter($promotion_content);
        }
        $show_newsletter_form = $this->_ves->getGeneralCfg("promotion_settings/show_newsletter");
        $show_dontshow_button = $this->_ves->getGeneralCfg("promotion_settings/show_dontshow");
        $timeout = $this->_ves->getGeneralCfg("promotion_settings/timeout");
        $popup_width = $this->_ves->getGeneralCfg("promotion_settings/popup_width");
        $popup_height = $this->_ves->getGeneralCfg("promotion_settings/popup_height");

        
        $this->assign('popup_id', $popup_id);
        $this->assign('padding', $padding);
        $this->assign('prefix_class', $prefix_class);
        $this->assign('promotion_content', $promotion_content);
        $this->assign('show_newsletter_form', $show_newsletter_form);
        $this->assign('show_dontshow_button', $show_dontshow_button);
        $this->assign('timeout', $timeout);
        $this->assign('popup_width', $popup_width);
        $this->assign('popup_height', $popup_height);

        if(!$this->getTemplate()){
           $this->setTemplate("Ves_Themesettings::html/popup.phtml");
        }
        return parent::_toHtml();
    }
}