<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Block;
 
use \Magento\Framework\View\Element\Template\Context;
use \Mageants\BannerSlider\Helper\Data;
use \Magento\Framework\ObjectManagerInterface;
use \Mageants\BannerSlider\Model\SlidersFactory;
use \Mageants\BannerSlider\Model\SlidesFactory;
use \Mageants\BannerSlider\Model\ResourceModel\Image;
use \Magento\Store\Model\StoreManagerInterface;
use \Mageants\BannerSlider\Model\Source\SlideType;
use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use \Magento\Catalog\Model\ResourceModel\Category;

/**
 * Class BlockRepository
 *
 * @package Mageants\BannerSlider\Block
 */
 
class BannerSlider extends \Magento\Framework\View\Element\Template
{
	/**
     * @var _storeManager
     */
	protected $_storeManager;
	/**
     * @var _objectManager
     */
	protected $_objectManager;
	/**
     * @var _slidersFactory
     */
	protected $_slidersFactory;
	/**
     * @var _slidesFactory
     */
	protected $_slidesFactory;
	/**
     * @var _imageFactory
     */
	protected $_imageFactory;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;
	/**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;
	
	/**
     * @var _sliderid
     */
	protected $_sliderid;
	
	/**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;
	
    /**
     * @param Context $context,
	 * @param array $data = [],
	 * @param Data $helper,
	 * @param ObjectManagerInterface $objectManager,
	 * @param SlidersFactory $slidersFactory,
	 * @param SlidesFactory $slidesFactory,
	 * @param Image $imageFactory,
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $productCollectionFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
	public function __construct(
		Context $context,
		array $data = [],
		Data $helper,
		ObjectManagerInterface $objectManager,
		\Magento\Cms\Model\Template\FilterProvider $filterProvider,
		SlidersFactory $slidersFactory,
		SlidesFactory $slidesFactory,
		Image $imageFactory,
		CollectionFactory $productCollectionFactory,
		Category\CollectionFactory $categoryCollectionFactory
	) 
	{	
		parent::__construct($context, $data);
		
		$this->_helper = $helper;
		
		$this->_storeManager = $context->getStoreManager();
		
		$this->_objectManager = $objectManager;
	 	
		$this->_slidersFactory = $slidersFactory;
		
		$this->_slidesFactory = $slidesFactory;
		
		$this->_imageFactory = $imageFactory;
		
		$this->_productCollectionFactory = $productCollectionFactory;
		
		$this->_categoryCollectionFactory = $categoryCollectionFactory;
		
		$this->_sliderid = $data['slider_id']; 

		$this->_filterProvider = $filterProvider;
		
	}
	
	/**
     * Retrieve slider for current request 
     *
     * @return slider
     */
    public function getSlider()
    {
		$slider = false;
		
		$sliderFactory = $this->_slidersFactory->create();
		
		if($this->_sliderid)
		{	
			$slider = $sliderFactory->getCollection()
							->addFieldToFilter('id', array('eq' => $this->_sliderid))
							->addFieldToFilter('store_id', array('in' => array($this->getCurrentStoreId(),0)))
							->addFieldToFilter('status', array('eq' => 1))
							->getFirstItem();							
		}
		
		return $slider;
    }
	 
	/**
     * Retrieve slide list of current slider
     *
     * @return Slides[]
     */
    public function getSlides()
    {
		$slides = array();
		
		$helper = $this->getBannerHelper();
		
		$imageHelper = $this->getBannerImageHelper();
		
		$slidesFactory = $this->_slidesFactory->create();
		
		if($this->_sliderid)
		{
			$slidesCollection = $slidesFactory->getCollection()
							->addFieldToFilter('slider_id', array('in' => $this->_sliderid))
							->addFieldToFilter('status', array('eq' => 1))
							->setOrder('position','ASC');
							
			foreach($slidesCollection as $slide)
			{
				$content = $slide->getContent();
				$filterContent = $this->_filterProvider->getPageFilter()->filter($content);

				$unser_slidesetting = $helper->unserializeSetting($slide->getSlidesetting());
				
				$slide_settings = $unser_slidesetting['setting'];
			
				switch($slide->getSlideType())
				{
					
					case SlideType::SLIDE_IMAGE :
		
						$slides[] = [
								 'image'=> $imageHelper->getBannerUrl( $slide->getImage() ) ,
								 'setting' => $slide_settings ,
								 'title' =>  $slide->getTitle()  ,
								 'content' =>  $filterContent  ,
								 'url' => $slide->getId()  ,
								 'class' => "image_slide"
							];
						
					break;
					
					case SlideType::SLIDE_CATEGORY : 
					
						$cat_collection = $this->_categoryCollectionFactory->create();
						
						$cat_collection->addAttributeToSelect('*');
						
						$categoryIds = explode(',' , $slide->getCategoryIds());
						
						$cat_collection->addIdFilter($categoryIds);
						foreach($cat_collection as $category )
						{
							$cat_image = $category->getImageUrl() ;
							if($slide->getShowCatSlideIfNoImageFound() )
							{
								$slides[] = [
									 'image'=>	 $cat_image ,
									 'setting' => $slide_settings ,
									 'title' => $category->getName()  ,
									 'content' =>  $category->getDescription()  ,
									 'url' => $slide->getId() ,
									 'class' => "category_slide"
								];
							}
							else if($cat_image)
							{
								$slides[] = [
									 'image'=>	 $category->getImageUrl() ,
									 'setting' => $slide_settings ,
									 'title' => $category->getName()  ,
									 'content' =>  $category->getDescription()  ,
									 'url' => $slide->getId() ,
									 'class' => "category_slide"
								];
							}
						}
					break;
					
					case SlideType::SLIDE_PRODUCT : 
						
						$prod_collection = $this->_productCollectionFactory->create();
						
						$prod_collection->addAttributeToSelect('*');
										
						$productIds = explode(',' , $slide->getProductIds());
						
						$prod_collection->addIdFilter($productIds);
						
						foreach($prod_collection as $product )
						{
							$image = $product->getImage();
							
							if($slide->getShowProdSlideIfNoImageFound() )
							{
								$prod_image = $this->_imageFactory->getProductImageUrl( $product->getImage());
							
								$slides[] = [
										 'image'=>	$prod_image,
										 'setting' => $slide_settings ,
										 'title' => $product->getName() ,
										 'content' =>  $product->getShortDescription() ,
										 'url' => $slide->getId() ,
										 'class' => "product_slide"
									];
							}
							else if($image)
							{
								$slides[] = [
										 'image'=>	'',
										 'setting' => $slide_settings ,
										 'title' => $product->getName() ,
										 'content' =>  $product->getShortDescription() ,
										 'url' => $slide->getId()  ,
										 'class' => "product_slide"
									];
							} 
						}
					break; 
				} 
			}
		}
		
		
		return $slides;
    } 
	
	/**
     * Retrieve Module Data Helper
     *
     * @return _helper
     */
	public function getBannerHelper()
	{
		return $this->_helper;
	}
	
	/**
     * Retrieve Image Model 
     *
     * @return imageFactory
     */
	public function getBannerImageHelper()
	{
		return $this->_imageFactory;
	}
	
	/**
     * Retrieve current Store Id 
     *
     * @return store_id
     */
	public function getCurrentStoreId(){
		return $this->_storeManager->getStore()->getId();
	}
}