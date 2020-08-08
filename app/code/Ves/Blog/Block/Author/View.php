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
namespace Ves\Blog\Block\Author;

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

    /**
     * @param \Magento\Framework\View\Element\Template\Context
     * @param \Magento\Framework\Registry
     * @param \Ves\Blog\Model\Post
     * @param \Ves\Blog\Helper\Data
     * @param array
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Ves\Blog\Model\Post $postFactory,
        \Ves\Blog\Helper\Data $blogHelper,
        array $data = []
        ) {
        $this->_blogHelper = $blogHelper;
        $this->_coreRegistry = $registry;
        $this->_postFactory = $postFactory;
        parent::__construct($context, $data);

    }

    public function getConfig($key, $default = '')
    {
        if($this->hasData($key)){
            return $this->getData($key);
        }
        $result = $this->_blogHelper->getConfig($key);
        $c = explode("/", $key);
        if($this->hasData($c[1])){
            return $this->getData($c[1]);
        }
        if($result == ""){
            $this->setData($c[1], $default);
            return $default;
        }
        $this->setData($c[1], $result);
        return $result;
    }

    public function getAuthor(){
        $author = $this->_coreRegistry->registry('author');
        return $author;
    }

    public function _construct()
    {
        parent::_construct();
    }

    public function _toHtml(){
        if(!$this->getConfig('general_settings/enable')){
            return;
        }
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
        $author = $this->getAuthor();
        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $brandRoute = $this->getConfig('general_settings/route');
        $page_title = $author->getFirstname() . ' ' . $author->getLastName();
        $show_breadcrumbs = $this->getConfig('blog_page/show_breadcrumbs');

        if($show_breadcrumbs && $breadcrumbsBlock){
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $baseUrl
                ]
             );

            $breadcrumbsBlock->addCrumb(
                'latest',
                [
                    'label' => __('Blog'),
                    'title' => __('Return to Blog'),
                    'link' => $this->_blogHelper->getLatestPageUrl()
                ]
            );

            $breadcrumbsBlock->addCrumb(
                'vesblog',
                [
                    'label' => $page_title,
                    'title' => $page_title,
                    'link' => ''
                ]
             );
        }
    }

    /**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {   
        $author = $this->getAuthor(); 
        $page_title = 'Author: ' . $author->getFirstname() . ' ' . $author->getLastName();
        $this->_addBreadcrumbs();
        $this->pageConfig->addBodyClass('blogauthor-' . $author->getUsername());
        if($page_title){
            $this->pageConfig->getTitle()->set($page_title);   
        }
        return parent::_prepareLayout();
    }

    /**
     * Set brand collection
     * @param \Ves\Blog\Model\Author
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
        $show_toolbartop = $this->_blogHelper->getConfig("blog_page/show_toolbartop");
        $show_toolbarbottom = $this->_blogHelper->getConfig("blog_page/show_toolbartop");
        $layout_type = $this->_blogHelper->getConfig("blog_page/layout_type");
        $this->setData('show_toolbartop', $show_toolbartop);
        $this->setData('show_toolbarbottom', $show_toolbarbottom);
        $this->setData('layout_type', $layout_type);
        
        $postsBlock = $this->getLayout()->getBlock('blog.posts.list');
        $this->_postsBlock = $postsBlock;
        $data = $postsBlock->getData();
        unset($data['type']);
        $this->addData($data);

        $store = $this->_storeManager->getStore();
        $author = $this->getAuthor();
        $itemsperpage = (int)$this->getConfig('blog_page/item_per_page');
        $orderby = $this->getConfig('blog_page/orderby');
        $postCollection = $this->_postFactory->getCollection()
        ->addFieldToFilter('is_active',1)
        ->addFieldToFilter('user_id', $author->getId())
        ->addStoreFilter($store)
        ->setPageSize($itemsperpage)
        ->setCurPage(1);

        $postCollection->getSelect()->order("post_id " . $orderby);

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