<?php
/**
 * Iksanika llc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.iksanika.com/products/IKS-LICENSE.txt
 *
 * @category   Iksanika
 * @package    Iksanika_Ordermanage
 * @copyright  Copyright (c) 2015 Iksanika llc. (http://www.iksanika.com)
 * @license    http://www.iksanika.com/products/IKS-LICENSE.txt
 */
namespace Iksanika\Productmanage\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Config;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;


class SaveConfig extends \Magento\Catalog\Controller\Adminhtml\Product
{

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        Action\Context $context,
        Product\Builder $productBuilder,
        \Magento\Framework\App\Config $config,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Iksanika\Productmanage\Helper\Data $helper,

        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,

        \Magento\Framework\App\Cache\Manager $cacheManager,
        \Magento\Framework\App\Cache\Type\Config $cacheTypeConfig

    ) {
        $this->productBuilder = $productBuilder;
        parent::__construct($context, $productBuilder);
        $this->_helperData = $helper;
        $this->_helperData->setScopeConfig($config);
        $this->_scopeConfig = $config;
        $this->_resourceConfig = $resourceConfig;

        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;

        $this->_cacheManager = $cacheManager;
        $this->_cacheTypeConfig = $cacheTypeConfig;
    }
     
    protected function saveConfig($pathId, $value, $scope = 'default', $scopeId = 0)
    {
        $this->_resourceConfig->saveConfig($pathId, $value, $scope, $scopeId);
    }

    /**
     * Update product(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $settingsFields = $this->getRequest()->getParam('settings', array());
        
        $this->saveConfig('iksanika_productmanage/images/width', $settingsFields['images']['width']);
        $this->saveConfig('iksanika_productmanage/images/height', $settingsFields['images']['height']);
        $this->saveConfig('iksanika_productmanage/images/scale', $settingsFields['images']['scale']);
        $this->saveConfig('iksanika_productmanage/images/showurl', $settingsFields['images']['showurl']);
        $this->saveConfig('iksanika_productmanage/columns/associatedShow', $settingsFields['columns']['associatedShow']);
        $this->saveConfig('iksanika_productmanage/columns/redirectAdvancedProductManager', $settingsFields['columns']['redirectAdvancedProductManager']);
//        $resetPositions = (Mage::getStoreConfig('productupdater/columns/showcolumns') != $settingsFields['columns']['showcolumns']) ? true : false;
        $this->saveConfig('iksanika_productmanage/columns/showcolumns', $settingsFields['columns']['showcolumns']);
//        if($resetPositions)
//        {
//            $config->saveConfig('productupdater/attributes/positions' . Mage::getSingleton('admin/session')->getUser()->getId(), '');
//        }

        $this->_cacheTypeList->cleanType('config');

        //// $this->_cacheManager->clean([\Magento\Framework\App\Cache\Type\Config::CACHE_TAG]);
        //        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
        //            $cacheFrontend->getBackend()->clean();
        //        }

        $result = array('success' => 1);

        $this->getResponse()->representJson(
            $this->_objectManager->create('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
    }

    
}