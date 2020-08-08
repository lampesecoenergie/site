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
 * @package    Ves_Blog
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Blog\Block\Category;

class View extends \Magento\Framework\View\Element\Template
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_blogHelper;

    /**
     * @var \Ves\Blog\Model\Post
     */
    protected $_postFactory;
    protected $_collection;
    protected $_postsBlock;
    protected $_resource;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context      
     * @param \Magento\Framework\Registry                      $registry     
     * @param \Ves\Blog\Model\Post                             $postFactory  
     * @param \Ves\Blog\Helper\Data                            $blogHelper   
     * @param \Magento\Framework\App\ResourceConnection        $resource
     * @param array                                            $data         
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Ves\Blog\Model\Post $postFactory,
        \Ves\Blog\Helper\Data $blogHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
        ) {
        $this->_blogHelper = $blogHelper;
        $this->_coreRegistry = $registry;
        $this->_postFactory = $postFactory;
        $this->_resource = $resource;
        parent::__construct($context, $data);
    }

    public function getConfig($key, $default = '')
    {
        if($this->hasData($key)){
            return $this->getData($key);
        }
        $result = $this->_blogHelper->getConfig($key);
        $c = explode("/", $key);
        if(count($c)==2){
            if($this->hasData($c[1])){
                return $this->getData($c[1]);
            }
            if($result == ""){
                $this->setData($c[1], $default);
                return $default;
            }
            $this->setData($c[1], $result);
        }
        return $result;
    }

    public function _toHtml(){
        $category = $this->getCategory();
        if(!$this->getConfig('general_settings/enable') || !$category->getIsActive()) return;
        return parent::_toHtml();
    }

    /**
     * Prepare breadcrumbs
     *
     * @param \Magento\Cms\Model\Page $brand
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _addBreadcrumbs()
    {
        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $brandRoute = $this->getConfig('general_settings/route');
        $category = $this->getCategory();
        $page_title = $category->getPageTitle();
        $show_breadcrumbs = $this->getConfig('category_page/show_breadcrumbs');
        if($show_breadcrumbs && $breadcrumbsBlock){
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link'  => $baseUrl
                ]
                );

            $breadcrumbsBlock->addCrumb(
                'latest',
                [
                'label' => __('Blog'),
                'title' => __('Return to Blog'),
                'link'  => $this->_blogHelper->getLatestPageUrl()
                ]
                );

            $breadcrumbsBlock->addCrumb(
                'vesblog',
                [
                'label' => $page_title,
                'title' => $page_title,
                'link'  => ''
                ]
                );
        }
    }

    /**
     * Set brand collection
     * @param \Ves\Blog\Model\Category
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;
        return $this->_collection;
    }

    public function getCollection(){
        return $this->_collection;
    }

    /**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {   
        $category = $this->getCategory();
        $page_title = $category->getPageTitle();
        $meta_description = $category->getMetaDescription();
        $meta_keywords = $category->getMetaKeywords();

        $this->_addBreadcrumbs();
        $this->pageConfig->addBodyClass('blog-cat-' . $category->getIdentifier());
        if($page_title){
            $this->pageConfig->getTitle()->set($page_title);   
        }
        if($meta_keywords){
            $this->pageConfig->setKeywords($meta_keywords);   
        }
        if($meta_description){
            $this->pageConfig->setDescription($meta_description);   
        }
        return parent::_prepareLayout();
    }

    public function getCategory(){
        $category = $this->_coreRegistry->registry('current_post_category');
        return $category;
    }

    /**
     * Retrieve Toolbar block
     *
     * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    public function getToolbarBlock()
    {
        $block = $this->getLayout()->getBlock('vesblog_toolbar');
        if ($block) {
            return $block;
        }
    }

    public function getPostsBlock()
    {
        $collection = $this->getCollection();
        $block = $this->_postsBlock;
        $block->setData($this->getData())->setCollection($collection);
        $html = $block->toHtml();
        if ($html) {
            return $html;
        }   
    }

    /**
     * Need use as _prepareLayout - but problem in declaring collection from
     * another block (was problem with search result)
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $category = $this->getCategory();
        $show_toolbartop = $this->_blogHelper->getConfig("category_page/show_toolbartop");
        $show_toolbarbottom = $this->_blogHelper->getConfig("category_page/show_toolbartop");
        $this->setData('show_toolbartop', $show_toolbartop);
        $this->setData('show_toolbarbottom', $show_toolbarbottom);
        $this->addData($category->getData());

        $postsBlock = $this->getLayout()->getBlock('blog.posts.list');
        $this->_postsBlock = $postsBlock;
        $data = $postsBlock->getData();
        unset($data['type']);
        $this->addData($data);
        
        $store = $this->_storeManager->getStore();
        $itemsperpage = (int)$this->getConfig('item_per_page');
        $orderby = $category->getOrderby();
        $postCollection = $this->_postFactory->getCollection()
        ->addFieldToFilter('is_active',1)
        ->setPageSize($itemsperpage)
        ->addStoreFilter($store)
        ->setCurPage(1);
        $postCollection->getSelect()
        ->joinLeft(
            [
            'cat' => $this->_resource->getTableName('ves_blog_post_category')],
            'cat.post_id = main_table.post_id',
            [
            'post_id' => 'post_id',
            'position' => 'position'
            ]
            )
        ->where('cat.category_id = (?)', (int)$category->getCategoryId());
        /*$postCollection->getSelect()->order('cat.position ASC')
        ->order('main_table.post_id DESC');*/

        if($orderby == 1){
            $postCollection->getSelect()->order('main_table.post_id DESC');
        }else if($orderby == 2){
            $postCollection->getSelect()->order('main_table.post_id ASC');
        }else if($orderby == 3){
            $postCollection->getSelect()->order('cat.position DESC');
        }else if($orderby == 4){
            $postCollection->getSelect()->order('cat.position ASC');
        }

        $postCollection->getSelect()->group('main_table.post_id');

        /*if($orderby == 1){
            $postCollection->getSelect()->order('creation_time DESC');
        }else if($orderby == 2){
            $postCollection->getSelect()->order('creation_time ASC');
        }else if($orderby == 3){
            $postCollection->getSelect()->order('position DESC');
        }else if($orderby == 4){
            $postCollection->getSelect()->order('position ASC');
        }*/
        $this->setCollection($postCollection);

        $toolbar = $this->getToolbarBlock();
        // set collection to toolbar and apply sort
        if($toolbar){
            $toolbar->setData('_current_limit',$itemsperpage)->setCollection($postCollection);
            $this->setChild('toolbar', $toolbar);
        }
        return parent::_beforeToHtml();
    }
}