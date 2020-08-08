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
class Head extends \Magento\Framework\View\Element\Template
{
	/**
     * @var \Magento\Framework\View\Page\Config
     */
	protected $pageConfig;

	/**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
	protected $_storeManager;

	/**
     * @var \Ves\Themesettings\Helper\Data
     */
	protected $_helper;

	/**
	 * @param \Magento\Framework\View\Element\Template\Context                   $context          
	 * @param \Ves\Themesettings\Model\System\Config\Source\Css\Font\GoogleFonts $_googleFontModel 
	 * @param \Ves\Themesettings\Helper\Theme                                    $ves    
	 * @param \Ves\Themesettings\Helper\Data                                     $_helper             
	 * @param array                                                              $data             
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Ves\Themesettings\Model\System\Config\Source\Css\Font\GoogleFonts $_googleFontModel,
		\Ves\Themesettings\Helper\Theme $ves,
		\Ves\Themesettings\Helper\Data $_helper,
		array $data = []
		){
		parent::__construct($context, $data);

		$googleLinks = [];
		$this->_helper = $_helper;

		$_pageConfig = $context->getPageConfig();
		$direction = $ves->getGeneralCfg('general_settings/direction');
		$body_classes = $ves->getGeneralCfg('general_settings/body_classes');
		$_pageConfig->setElementAttribute(
			\Magento\Framework\View\Page\Config::ELEMENT_TYPE_HTML,
			"dir",
			$direction
			);

		$_pageConfig->setElementAttribute(
			\Magento\Framework\View\Page\Config::ELEMENT_TYPE_HTML,
			"class",
			$direction
			);
		//$_pageConfig->addBodyClass($direction);
		$_pageConfig->addBodyClass($body_classes);

		$layout = $ves->getGeneralCfg('general_settings/layout');
		$_pageConfig->addBodyClass($layout);

		$store = $this->_storeManager->getStore();
		$_pageConfig->addPageAsset("Ves_Themesettings::css/style-".$store->getCode().".css",[
			"attributes" => [ "media" => "all" ]
			]);

		// Skins
		$skin = $ves->getGeneralCfg('general_settings/skin');
		if($ves->getGeneralCfg("general_settings/paneltool")){
            $skin_request = $this->getRequest()->getParam('skin_color', $this->getRequest()->getParam('skin', false));
            $skin_request = trim($skin_request);
            if($skin_request) {
                $skin = $skin_request.".css";
            }
        }
		if($skin!=''){
			$_pageConfig->addPageAsset("Ves_Themesettings::css/skins/".$skin,[
				"attributes" => [ "media" => "all" ]
				]);
		}

        //Include Google Fonts
		$amp = '&amp;';
		$charSubset = '';
		if ($subsets = $ves->getGeneralCfg('font/body_char_subset')){
			$charSubset = "{$amp}subset={$subsets}";
		}
		$fontWeight = '';
		$weight = $ves->getGeneralCfg('font/body_font_weight');
		if ($weight){
			$fontWeight = ':' . $weight;
		}
		$fonts = [];
		// Body Font Settings
		if($ves->getGeneralCfg('font/body_font_family_group') == 'google'){
			$googleLink = 'https://fonts.googleapis.com/css?family='.str_replace(' ', '+', $ves->getGeneralCfg('font/body_font_family')).$fontWeight.$charSubset;
			if(!in_array($googleLink, $googleLinks)){
				$this->pageConfig->addRemotePageAsset($googleLink,'css',['attributes'=>[]]);
			}
		}

		// Custom Fonts
		$customFonts = $ves->getGeneralCfg('font/custom_fonts');
		if($customFonts) {
			if(!is_array($customFonts)) {
				$customFonts = $this->_helper->getUnserializeText($customFonts);
			}
			
			if(is_array($customFonts)){
				unset($customFonts['__empty']);
				if($customFonts) {
					foreach ($customFonts as $_font) {
						if($_font['classes'] == '') continue;
						if($_googleFontModel->isGoogleFont($_font['font'])){
							$googleLink = $fontWeight = '';
							$fontWeight = ':' . $_font['weight'];
							$googleLink = 'https://fonts.googleapis.com/css?family='.$_font['font'].$fontWeight;
							if(!in_array($googleLink, $googleLinks)){
								$this->pageConfig->addRemotePageAsset($googleLink,'css',['attributes'=>[]]);
							}
						}
					}
				}
			}
		}

		//Product Name Font
		$show_name = $ves->getProductPageCfg('element_settings/show_name');
		$enable_customfont = $ves->getProductPageCfg('element_settings/enable_customfont');
		$amp = '&amp;';
		$charSubset = '';
		if ($subsets = $ves->getProductPageCfg('element_settings/name_char_subset')){
			$charSubset = "{$amp}subset={$subsets}";
		}
		$fontWeight = '';
		$weight = $ves->getProductPageCfg('element_settings/name_font_weight');
		if ($weight){
			$fontWeight = ':' . $weight;
		}
		$fonts = [];
		// Product Name Font Settings
		if($show_name && $enable_customfont && $ves->getProductPageCfg('element_settings/name_font_family_group') == 'google'){
			$googleLink = 'https://fonts.googleapis.com/css?family='.str_replace(' ', '+', $ves->getProductPageCfg('element_settings/name_font_family')).$fontWeight.$charSubset;
			if(!in_array($googleLink, $googleLinks)){
				$this->pageConfig->addRemotePageAsset($googleLink,'css',['attributes'=>[]]);
			}
		}
	}

	/**
     * Process asset properties
     *
     * @param array $data
     * @return array
     */
	protected function getAssetProperties(array $data = [])
	{
		$properties = [];
		$attributes = [];
		foreach ($data as $name => $value) {
			if (in_array($name, $this->assetProperties)) {
				$properties[$name] = $value;
			} elseif (!in_array($name, $this->serviceAssetProperties)) {
				$attributes[$name] = $value;
			}
		}
		$properties['attributes'] = $attributes;
		return $properties;
	}
}