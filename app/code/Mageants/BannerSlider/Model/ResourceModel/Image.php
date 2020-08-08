<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Model\ResourceModel;

use \Magento\Framework\UrlInterface;
use \Magento\Framework\Filesystem;
use \Magento\Framework\View\Asset\Repository;
use \Magento\Store\Model\StoreManagerInterface;
		
class Image
{
	
	/**
     * @var _storeManager
     */
	protected $_storeManager;
    /**
     * Media sub folder
     * 
     * @var string
     */
    protected $_subDir = 'mageants/bannerslider/slides';

    /**
     * URL builder
     * 
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * File system model
     * 
     * @var \Magento\Framework\Filesystem
     */
    protected $_fileSystem;

    /**
     * @var \Magento\Framework\View\Asset\Repositoryp
     */
    protected $_assetRepo;

    /**
     * constructor
     * 
     * @param UrlInterface $urlBuilder
     * @param Filesystem $fileSystem
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Filesystem $fileSystem,
		Repository $assetRepo,
		StoreManagerInterface $storeManager
    )
    {
        $this->_urlBuilder = $urlBuilder;
		
        $this->_fileSystem = $fileSystem;
		
		$this->_assetRepo = $assetRepo;
		
		$this->_storeManager = $storeManager;
    }

    /**
     * get images base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]);
       /* echo $this->_urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]).$this->_subDir.'/image';
        exit();*/
    }
    
	/**
     * get base image dir
     *
     * @return string
     */
    public function getBaseDir()
    {
        return $this->_fileSystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath($this->_subDir.'/image');
    }
	
    /**
     * get base image dir
     *
     * @return string
     */
    public function getBannerUrl($bannerImage)
    {
        return $this->getBaseUrl().$bannerImage;
    }
	
    /**
     * get category tree icon
     *
     * @return string
     */
    public function getCategoryTreeIcon()
    {
        return $this->_assetRepo->getUrl("Mageants_BannerSlider::images/category_tree.png");
    }
	
	/**
     * get product icon
     *
     * @return string
     */
    public function getProductsIcon()
    {
        return $this->_assetRepo->getUrl("Mageants_BannerSlider::images/products.png");
    }
	
	/**
     * get product icon
     *
     * @return string
     */
    public function getProductImageUrl($imagePath)
    {
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA). "/catalog/product" .$imagePath;
    }
	
	
}
