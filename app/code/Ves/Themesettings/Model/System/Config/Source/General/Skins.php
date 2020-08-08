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
namespace Ves\Themesettings\Model\System\Config\Source\General;

class Skins implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_request;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Theme\Model\Theme
     */
    protected $_themeModel;

    /**
     * @var \Magento\Framework\View\Design\Theme\Customization\Path
     */
    protected $_path;

    /**
     * @param \Magento\Cms\Model\Block $blockModel
     */

    public function __construct(
        \Ves\Themesettings\Helper\Data $vesHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Theme\Model\Theme $themeModel,
        \Magento\Framework\View\Design\Theme\Customization\Path $path
        ) {
        $this->_request = $request;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_themeModel = $themeModel;
        $this->_path = $path;
        $this->_vesHelper = $vesHelper;
    }

    public function toOptionArray()
    {
        $output = [];
        $theme = '';
        $storeId = $this->_request->getParam('store');
        $websiteId = $this->_request->getParam('website');
        $website = $this->_storeManager->getWebsite($websiteId);
        if(!$storeId && $website){
            $storeId = $website->getDefaultStore()->getId();
        }
        $store = $this->_storeManager->getStore($storeId);
        $themeId =  $this->_scopeConfig->getValue(
            \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store);

        if(!empty($this->_vesHelper->getVesTheme($storeId, $themeId))){
            $theme = $this->_themeModel->load($themeId);
            $themePath = $this->_path->getThemeFilesPath($theme);
            $skinDir = $themePath.'/Ves_Themesettings/web/css/skins/';

            $skins = glob($skinDir . '*.css');

            $parentSkinDir = "";
            if($themeParentId = $theme->getParentId()){
                $parent_theme = $this->_themeModel->load($themeParentId);
                $parentThemePath = $this->_path->getThemeFilesPath($parent_theme);
                $parentSkinDir = $parentThemePath.'/Ves_Themesettings/web/css/skins/';
            }
            //If child theme dont have skin, get from parent theme
            if($parentSkinDir && (!$skins || count($skins) <= 0)) {
                $skins = glob($parentSkinDir . '*.css');
                $skinDir = $parentSkinDir;
            }

            $output[] = [
                'label' => __('Default'),
                'value' => ''
            ];
            $replaceLabelPattern = [$skinDir => '','.css'=>''];
            $replaceValuePattern = [$skinDir => ''];
            foreach ($skins as $k => $v) {
                $output[] = [
                'label' => ucfirst(str_replace(array_keys($replaceLabelPattern),array_values($replaceLabelPattern),$v)),
                'value' => str_replace(array_keys($replaceValuePattern),array_values($replaceValuePattern),$v)
                ];
            }
        }
        return $output;
    }

}