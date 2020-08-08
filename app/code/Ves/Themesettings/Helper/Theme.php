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
namespace Ves\Themesettings\Helper;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

class Theme extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Theme
     */
    private $currentTheme;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;
    /**
     * @var \Ves\Themesettings\Helper\Data
     */
    protected $helperData;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\DesignInterface $design
     * @param PhpCookieManager $cookieManager
     * @param \Ves\Themesettings\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\DesignInterface $design,
        PhpCookieManager $cookieManager,
        \Ves\Themesettings\Helper\Data $helperData,
        array $data = []
        ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->currentTheme = $design->getDesignTheme();
        $this->_coreRegistry = $registry;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->cookieManager = $cookieManager;
        $this->helperData = $helperData;
    }

    public function getCurrentTheme()
    {
        return $this->currentTheme;
    }

    public function getConfig($key, $package, $storeId = NULL, $default = NULL)
    {
        $store = $this->_storeManager->getStore($storeId);
        if($this->_coreRegistry->registry('ves_store')){
            $store = $this->_coreRegistry->registry('ves_store');
        }

        $vesCookie = $this->cookieManager->getCookie('vespaneltool');
        if($vesCookie){
            $settings = $this->helperData->getUnserializeText($vesCookie);
            if(isset($settings[$package.'/'.$key])){
                return $settings[$package.'/'.$key];
            }
        }

        $result = $this->scopeConfig->getValue(
            $package.'/'.$key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store);

        if($result == '' && $default){
            return $default;
        }
        return $result;
    }

    public function getGeneralCfg($group, $storeId = NULL, $default = NULL )
    {
        return $this->getConfig($group, "ves_themesettings_general", $storeId, $default);
    }

    public function getHeaderCfg($group, $storeId = NULL, $default = NULL )
    {
        return $this->getConfig($group, "ves_themesettings_header", $storeId, $default);
    }

    public function getFooterCfg($group, $storeId = NULL, $default = NULL )
    {
        return $this->getConfig($group, "ves_themesettings_footer", $storeId, $default);
    }

    public function getProductListingCfg($group, $storeId = NULL, $default = NULL )
    {
        return $this->getConfig($group, "ves_themesettings_product_listing", $storeId, $default);
    }

    public function getCategoryPageCfg($group, $storeId = NULL, $default = NULL )
    {
        return $this->getConfig($group, "ves_themesettings_category_page", $storeId, $default);
    }

    public function getProductPageCfg($group, $storeId = NULL, $default = NULL )
    {
        $attribute_value = $this->getConfig($group, "ves_themesettings_product_page", $storeId, $default);
        switch($group) {
            case "tab_settings/product_attrs":
            case 'tab_settings/cms_block_ids':
                $attribute_value = $this->checkCurrentProductAttr($attribute_value);
            break;
        }
        return $attribute_value;
    }

    public function getContactPageCfg($group, $storeId = NULL, $default = NULL )
    {
        return $this->getConfig($group, "ves_themesettings_contact_page", $storeId, $default);
    }

    public function getCartPageCfg($group, $storeId = NULL, $default = NULL )
    {
        return $this->getConfig($group, "ves_themesettings_cart_page", $storeId, $default);
    }

    public function getCustomizationCfg($group, $storeId = NULL, $default = NULL )
    {
        return $this->getConfig($group, "ves_themesettings_customization", $storeId, $default);
    }

    public function checkCurrentProductAttr( $attribute_value  = '') {
        if($attribute_value!=''){
            $customAttribute = $this->helperData->getUnserializeText($attribute_value);

            if(is_array($customAttribute) && count($customAttribute)>0){
                $current_product = $this->_coreRegistry->registry('current_product');
                $sku = "";
                $category_ids = null;
                if($current_product) {
                    $sku = $current_product->getSku();
                    $category_ids = $current_product->getCategoryIds();
                    $tmp_custom_attributes = array();
                    foreach ($customAttribute as $k => $v) {
                        if(!isset($v['code'])) continue;
                        $check_current_product = true;
                        $check_current_category = true;
                        if(isset($v['category_ids']) || isset($v['product_skus'])) {
                            //Check category ids
                            if($v['category_ids'] && $category_ids) {
                                $arr_ids = explode(",", $v['category_ids']);
                                //Check current product category id in the list categories id or not
                                $result = array_intersect($arr_ids, $category_ids);
                                if(count($result) <= 0) {
                                    $check_current_category = false;
                                }
                            }

                            //Check product skus
                            if($v['product_skus'] && $sku) {
                                $arr_skus = explode(",", $v['product_skus']);
                                //Check current product sku in the list skus or not
                                if(!in_array($sku, $arr_skus)) {
                                    $check_current_product = false;
                                }
                            }
                        }
                        //If current product have skus or categories matched with tab setting, will add it on custom tab array
                        if($check_current_product || $check_current_category) {
                            $tmp_custom_attributes[] = $v;
                        }
                        
                    }
                    $attribute_value = serialize($tmp_custom_attributes);
                }
            }
        }
        return $attribute_value;
    }
}