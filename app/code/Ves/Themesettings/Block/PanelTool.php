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
namespace Ves\Themesettings\Block;

class PanelTool extends \Magento\Framework\View\Element\Template
{
	/**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
	protected $_storeManager;

	/**
	 * @var \Ves\Themesettings\Model\System\Config\Source\General\Skins
	 */
	protected $_skins;

	/**
	 * @var \Ves\Themesettings\Model\System\Config\Source\Header\Layouts
	 */
	protected $_headerLayout;

	/**
	 * @var \Ves\Themesettings\Helper\Theme
	 */
	protected $_ves;

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Ves\Themesettings\Model\System\Config\Source\General\Skins $skins,
		\Ves\Themesettings\Model\System\Config\Source\Header\Layouts $headerLayout,
		\Ves\Themesettings\Helper\Theme $ves,
		array $data = []
		){
		parent::__construct($context, $data);
		$this->_skins = $skins;
		$this->_headerLayout = $headerLayout;
		$this->_ves = $ves;
	}

	public function _toHtml(){
		$ves = $this->_ves;
		if(!$ves->getGeneralCfg("general_settings/paneltool")){
			return;
		}
		return parent::_toHtml();
	}

	public function getSkins(){
		return $this->_skins->toOptionArray();
	}

	public function getHeaderLayouts(){
		return $this->_headerLayout->toOptionArray();
	}

	public function getStoreSwitcherHtml(){
		$html = $this->getLayout()->createBlock('Magento\Store\Block\Switcher')->setTemplate('Ves_Themesettings::paneltool/stores.phtml')->toHtml();
		return $html;
	}

	public function getFormUrl(){
		$url = $this->getUrl('themesettings/index/paneltool');
		return $url;
	}
}