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
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Theme\Model\Theme
     */
    protected $_collectionThemeFactory;

    /**
     * File system
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    protected $_request;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $session;

    /**
     * Media directory read
     *
     * @var \Magento\Framework\Filesystem\Directory\Read
     */
    protected $vesthemeDirectory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /** @var \Magento\Customer\Helper\View */
    protected $_helperView;

    /**
     * @var Serialize
     */
    private $serializer;

     /**
     * @var Json
     */
    private $json;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $collectionThemeFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Theme\Model\Theme $themeModel,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Http\Context $httpContext,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Helper\View $helperView,
        Serialize $serializer = null,
        Json $json = null
        ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $registry;
        $this->_filterProvider = $filterProvider;
        $this->_collectionThemeFactory = $collectionThemeFactory;
        $this->_filesystem = $filesystem;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_themeModel = $themeModel;
        $this->_request = $context->getRequest();
        $this->session = $customerSession;
        $this->_objectManager = $objectManager;
        $this->httpContext = $httpContext;
        $this->customerRepository = $customerRepository;
        $this->_helperView = $helperView;
        $this->_categoryFactory = $categoryFactory;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Serialize::class);
        $this->json = $json ?: ObjectManager::getInstance()->get(Json::class);
    }

    public function getSerializeText($value = ''){
        return $this->serializer->serialize($value);
    }
    public function getUnserializeText($value = ''){
        if($this->is_serialized($value)) {
            return $this->serializer->unserialize($value);
        } else {
            return $this->getUnserializeJson($value);
        }
        
    }

    public function getSerializeJson($value = ''){
        return $this->json->serialize($value);
    }
    public function getUnserializeJson($value = ''){
        return $this->json->unserialize($value);
    }

    public function is_serialized($value, &$result = null)
    {
        // Bit of a give away this one
        if (!is_string($value))
        {
            return false;
        }
        // Serialized false, return true. unserialize() returns false on an
        // invalid string or it could return false if the string is serialized
        // false, eliminate that possibility.
        if ($value === 'b:0;')
        {
            $result = false;
            return true;
        }
        $length = strlen($value);
        $end    = '';
        switch ($value[0])
        {
            case 's':
                if ($value[$length - 2] !== '"')
                {
                    return false;
                }
            case 'b':
            case 'i':
            case 'd':
                // This looks odd but it is quicker than isset()ing
                $end .= ';';
            case 'a':
            case 'O':
                $end .= '}';
                if ($value[1] !== ':')
                {
                    return false;
                }
                switch ($value[2])
                {
                    case 0:
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                    case 8:
                    case 9:
                        break;
                    default:
                        return false;
                }
            case 'N':
                $end .= ';';
                if ($value[$length - 1] !== $end[0])
                {
                    return false;
                }
                break;
            default:
                return false;
        }
        if (($result = @unserialize($value)) === false)
        {
            $result = null;
            return false;
        }
        return true;
    }

    /**
     * Check if current url is url for home page
     *
     * @return bool
     */
    public function isHomePage()
    {
        $currentUrl = $this->getUrl('', ['_current' => true]);
        $urlRewrite = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        return $currentUrl == $urlRewrite;
    }

    public function getMediaUrl(){
        $url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $url;
    }

    public function getCoreRegistry(){
        return $this->_coreRegistry;
    }

    /**
     * Check product is new
     *
     * @param  Mage_Catalog_Model_Product $_product
     * @return bool
     */
    public function checkProductIsNew($_product = null) {
        $from_date = $_product->getNewsFromDate();
        $to_date = $_product->getNewsToDate();
        $is_new = false;
        $is_new = $this->isNewProduct($from_date, $to_date);
        $today = strtotime("now");

        if ($from_date && $to_date) {
            $from_date = strtotime($from_date);
            $to_date = strtotime($to_date);
            if ($from_date <= $today && $to_date >= $today) {
                $is_new = true;
            }
        }
        elseif ($from_date && !$to_date) {
            $from_date = strtotime($from_date);
            if ($from_date <= $today) {
                $is_new = true;
            }
        }elseif (!$from_date && $to_date) {
            $to_date = strtotime($to_date);
            if ($to_date >= $today) {
                $is_new = true;
            }
        }
        return $is_new;
    }

    public function isNewProduct( $created_date, $num_days_new = 3) {
        $check = false;

        $startTimeStamp = strtotime($created_date);
        $endTimeStamp = strtotime("now");

        $timeDiff = abs($endTimeStamp - $startTimeStamp);
        $numberDays = $timeDiff/86400;// 86400 seconds in one day

        // and you might want to convert to integer
        $numberDays = intval($numberDays);
        if($numberDays <= $num_days_new) {
            $check = true;
        }
        return $check;
    }

    public function subString($text, $length = 100, $replacer = '...', $is_striped = true) {
        $text = ($is_striped == true) ? strip_tags($text) : $text;
        if (strlen($text) <= $length) {
            return $text;
        }
        $text = substr($text, 0, $length);
        $pos_space = strrpos($text, ' ');
        return substr($text, 0, $pos_space) . $replacer;
    }

    public function filter($str)
    {
        $html = $this->_filterProvider->getPageFilter()->filter($str);
        return $html;
    }

    public function getExportPackages(){
        $themes = $this->_collectionThemeFactory->create();
        $file = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('design/frontend/');
        $vesPackagePaths = glob($file . '*/config.xml');
        $output = [];
        foreach ($vesPackagePaths as $k => $v) {
            $v = str_replace("config.xml", "", $v);
            $themes = array_filter(glob($v . '*'), 'is_dir');
            foreach ($themes as $k => $v) {
                $output[] = array(
                    'label' => ucfirst(str_replace($file, "", $v)),
                    'value' => str_replace($file, "", $v)
                    );
            }
        }
        return $output;
    }

    public function getAllStores() {
        $allStores = $this->_storeManager->getStores();
        $stores = array();
        foreach ($allStores as $_eachStoreId => $val)
        {
            $stores[]  = $this->_storeManager->getStore($_eachStoreId)->getId();
        }
        return $stores;
    }

    // Store code

    public function getVesTheme($storeId = NULL, $themeId = null){
        if(!$themeId) {
            $store = $this->_storeManager->getStore($storeId);
            
            $themeId =  $this->_scopeConfig->getValue(
                \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store);
        }
        $theme = $this->_themeModel->load($themeId);
        $parent_theme = false;
        if($themeParentId = $theme->getParentId()){
            $parent_theme = clone $this->_themeModel;
            $parent_theme = $parent_theme->load($themeParentId);
        }
        $themePaths = [];
        $file = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('design/frontend/');
        $vesPackagePaths = glob($file . '*/*/config.xml');

        foreach ($vesPackagePaths as $k => $v) {
            $tmp = str_replace($file, "", $v);
            $tmp = str_replace("/config.xml", "", $tmp);
            if($theme->getCode()){
                // if($theme->getCode() == $tmp ){
                    $themePaths[] = str_replace("/config.xml", "", $v);
                // } 

            }elseif($parent_theme->getCode()){
                if($parent_theme->getCode() == $tmp ){
                    $themePaths[] = str_replace("/config.xml", "", $v);
                }
            }else{
                $themePaths[] = $v;
            }
        }
        return $themePaths;
    }

    public function getCustomerDataUrl(){
        $url = $this->_storeManager
        ->getStore()
        ->getUrl('customer/section/load',["update_section_id"=>true,"sections"=>"cart"]);
        return $url;
    }

    public function getRefreshCartUrl(){
        $url = $this->_storeManager
        ->getStore()
        ->getUrl('checkout/cart/add', ['ves'=>1, 'refresh'=>1]);
        return $url;
    }

    public function getRequest() {
        return $this->_request;
    }

    public function getAddToCartUrl(\Magento\Catalog\Model\Product $_product){
        $url = $this->_storeManager
        ->getStore()
        ->getUrl('themesettings/index/quickview',["id"=>$_product->getId(), '_secure' => $this->getRequest()->isSecure()]);
        return $url;
    }

    public function getCustomerSession(){
        $customerSession = $this->_objectManager->create('Magento\Customer\Model\SessionFactory')->create();
        return $customerSession;     
     }

    public function isLoggedIn(){
        return $this->httpContext->getValue(Context::CONTEXT_AUTH);
    }

    public function getObjectManager(){
        return $this->_objectManager;
    }

    public function getCustomer()
    {
        $currentCustomer  = $this->getCustomerSession();
        $customer_id = $currentCustomer->getCustomerId();
        $currentCustomer = $this->customerRepository->getById($customer_id);
        return $currentCustomer;
    }

    public function getCustomerName() {
        $currentCustomer = $this->getCustomer();
        if($currentCustomer) {
            return $this->_helperView->getCustomerName($currentCustomer);
        }
        return "";
    }

    public function getCategoryCollection($catIds) {
        $storeId = $this->_storeManager->getStore()->getId();
        $collection = $this->_categoryFactory->create()->getCollection();
        $collection->addAttributeToSelect('name')
        ->addAttributeToSelect('is_active')
        ->addAttributeToFilter('entity_id', ['in' => $catIds])
        ->setStoreId($storeId);
        return $collection;
    }

}