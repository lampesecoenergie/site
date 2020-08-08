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
namespace Ves\Themesettings\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

class PanelTool extends \Magento\Catalog\Controller\Product\View
{
    /**
     * Session config
     *
     * @var ConfigInterface
     */
    protected $sessionConfig;

    /**
     * CookieManager
     *
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Magento\Catalog\Helper\Product\View
     */
    protected $viewHelper;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Magento\Catalog\Helper\Product\View $viewHelper
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Helper\Product\View $viewHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ves\Themesettings\Helper\Theme $themehelper,
        CookieMetadataFactory $cookieMetadataFactory,
        PhpCookieManager $cookieManager,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        PageFactory $resultPageFactory
        ) {
        parent::__construct($context, $viewHelper, $resultForwardFactory, $resultPageFactory);
        $this->viewHelper           = $viewHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory    = $resultPageFactory;
        $this->cookieManager        = $cookieManager;
        $this->_storeManager        = $storeManager;
        $this->cookieMetadataFacory = $cookieMetadataFactory;
        $this->_cacheTypeList       = $cacheTypeList;
        $this->_cacheFrontendPool   = $cacheFrontendPool;
        $this->_theme               = $themehelper;
    }

    public function execute(){
        $data = $this->getRequest()->getParams();
        $enable_paneltool = $this->_theme->getGeneralCfg('general_settings/paneltool');

        $types = ['config'];
        foreach ($types as $type) {
            $this->_cacheTypeList->cleanType($type);
        }
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
        if(!empty($data) && $data['vespanel'] && !$data['vesreset'] && $enable_paneltool){
            $this->_storeManager->setCurrentStore(1);
            $options = $data['userparams']?$data['userparams']:[];
            $this->cookieManager->deleteCookie("vespaneltool");
            $publicCookieMetadata = $this->cookieMetadataFacory->createPublicCookieMetadata();
            $publicCookieMetadata->setDuration("3600");
            $publicCookieMetadata->setHttpOnly(true);
            $publicCookieMetadata->setPath('/');
            $this->cookieManager->setPublicCookie("vespaneltool", serialize($options), $publicCookieMetadata);

        }
        if($data['vesreset']){
            $publicCookieMetadata = $this->cookieMetadataFacory->createPublicCookieMetadata();
            $publicCookieMetadata->setDuration("3600");
            $publicCookieMetadata->setHttpOnly(true);
            $publicCookieMetadata->setPath('/');
            $this->cookieManager->deleteCookie("vespaneltool", $publicCookieMetadata);
        }
        if(isset($data['store'])){
            $this->getResponse()->setRedirect($data['store']);
        }else{
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
        }
    }
}