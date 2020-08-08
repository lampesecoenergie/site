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
namespace Ves\Blog\Block\Post;

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
    protected $_cattegoryFactory;
    protected $_collection;
    protected $_postsBlock;

    /**
     * @param \Magento\Framework\View\Element\Template\Context
     * @param \Magento\Framework\Registry
     * @param \Ves\Blog\Model\Post
     * @param \Ves\Blog\Model\Category
     * @param \Ves\Blog\Helper\Data
     * @param array
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Ves\Blog\Model\Post $postFactory,
        \Ves\Blog\Model\Category $categoryFactory,
        \Ves\Blog\Helper\Data $blogHelper,
        array $data = []
        ) {
        $this->_blogHelper = $blogHelper;
        $this->_coreRegistry = $registry;
        $this->_postFactory = $postFactory;
        $this->_cattegoryFactory = $categoryFactory;
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

    public function _toHtml(){
        $post = $this->getPost();
        if(!$this->getConfig('general_settings/enable') || !$post->getIsActive()) return;
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
        $post = $this->getPost();
        $page_title = $post->getPageTitle();
        $show_breadcrumbs = $this->getConfig('post_page/show_breadcrumbs');
        $categoriesUrls = $this->getConfig('general_settings/categories_urls');
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

            $categories = $post->getPostCategories();
            if($categories && isset($categories[0]['identifier']) && $categories[0]['identifier']!=''){
                $categoryUrl = $categories[0]['identifier'] . '/';
                $breadcrumbsBlock->addCrumb(
                'category',
                [
                    'label' => $categories[0]['name'],
                    'title' => $categories[0]['name'],
                    'link'  => $this->_blogHelper->getCategoryUrl($categories[0]['identifier'])
                ]
                );
            }

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
     * @param \Ves\Blog\Model\Post
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
        $post = $this->getPost();
        $page_title = $post->getPageTitle();
        $meta_description = $post->getMetaDescription();
        $meta_keywords = $post->getMetaKeywords();

        $this->_addBreadcrumbs();
        $this->pageConfig->addBodyClass('blog-post-' . $post->getIdentifier());
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

    public function getPost(){
        $post = $this->_coreRegistry->registry('current_post');
        return $post;
    }
}