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
namespace Ves\Blog\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Group Collection
     */
    protected $_groupCollection;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    protected $_config = [];

    /**
     * Template filter factory
     *
     * @var \Magento\Catalog\Model\Template\Filter\Factory
     */
    protected $_templateFilterFactory;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    protected $_postCategories = NULL;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    protected $_postFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    protected $_request;

	/**
     * @param \Magento\Framework\App\Helper\Context
     * @param \Magento\Store\Model\StoreManagerInterface
     * @param \Magento\Cms\Model\Template\FilterProvider
     * @param \Magento\Framework\App\ResourceConnection
     * @param \Magento\Framework\UrlInterface
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     * @param \Magento\User\Model\User
     * @param \Ves\Blog\Model\Post
     * @param \Magento\Framework\Registry
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Url $url,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\User\Model\User $userFactory,
        \Ves\Blog\Model\Post $postFactory,
        \Magento\Framework\Registry $registry
        ) {
        parent::__construct($context);
        $this->_request = $context->getRequest();
        $this->_filterProvider = $filterProvider;
        $this->_storeManager = $storeManager;
        $this->_resource = $resource;
        $this->_frontendUrlBuilder = $url;
        $this->_userFactory = $userFactory;
        $this->_localeDate = $localeDate;
        $this->_postFactory = $postFactory;
        $this->_coreRegistry = $registry;
    }

    /**
     * Return brand config value by key and store
     *
     * @param string $key
     * @param \Magento\Store\Model\Store|int|string $store
     * @return string|null
     */
    public function getConfig($key, $store = null)
    {
        $store = $this->_storeManager->getStore($store);
        $websiteId = $store->getWebsiteId();

        $result = $this->scopeConfig->getValue(
            'vesblog/'.$key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store);
        return $result;
    }

    public function filter($str)
    {
        $html = $this->_filterProvider->getPageFilter()->filter($str);
        return $html;
    }

    public function getAuthorUrl($author)
    {
        $url = $this->_storeManager->getStore()->getBaseUrl();
        $url_prefix = $this->getConfig('general_settings/url_prefix');
        $url_suffix = $this->getConfig('general_settings/url_suffix');
        $categoriesUrls = $this->getConfig('general_settings/categories_urls');
        $urlPrefix = '';
        if($url_prefix){
            $urlPrefix = $url_prefix.'/';
        }
        return $url . $urlPrefix . 'author/' . $author->getUserName();
    }

    public function getCategoryUrl($alias)
    {
        $url = $this->_storeManager->getStore()->getBaseUrl();
        $url_prefix = $this->getConfig('general_settings/url_prefix');
        $url_suffix = $this->getConfig('general_settings/url_suffix');
        $urlPrefix = '';
        if($url_prefix){
            $urlPrefix = $url_prefix.'/';
        }
        return $url . $urlPrefix . $alias . $url_suffix;
    }

    public function getPostAuthor($post){
        $userId = $post->getUserId();
        $author = $this->_userFactory->load($userId);
        return $author;
    }

    public function getTagUrl($alias){
        $url = $this->_storeManager->getStore()->getBaseUrl();
        $url_prefix = $this->getConfig('general_settings/url_prefix');
        $url_suffix = $this->getConfig('general_settings/url_suffix');
        $urlPrefix = '';
        if($url_prefix){
            $urlPrefix = $url_prefix.'/';
        }
        return $url . $urlPrefix . 'tag/' . $alias;
    }

    public function getLatestPageUrl(){
        $url = $this->_storeManager->getStore()->getBaseUrl();
        $url_prefix = $this->getConfig('general_settings/url_prefix');
        $url_suffix = $this->getConfig('general_settings/url_suffix');
        $latestPageUrl = $this->getConfig('blog_latest_page/route');
        $urlPrefix = '';
        if($url_prefix){
            $urlPrefix = $url_prefix.'/';
        }
        return $url . $urlPrefix;
    }

    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    ) {
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);
        return $this->_localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }

    public function getFormatDate($date, $type = 'full'){
        $result = '';
        switch ($type) {
            case 'full':
            $result = $this->formatDate($date, \IntlDateFormatter::FULL);
            break;
            case 'long':
            $result = $this->formatDate($date, \IntlDateFormatter::LONG);
            break;
            case 'medium':
            $result = $this->formatDate($date, \IntlDateFormatter::MEDIUM);
            break;
            case 'short':
            $result = $this->formatDate($date, \IntlDateFormatter::SHORT);
            break;
        }
        return $result;
    }

    public function getPostUrl($post)
    {
        $url = $this->_storeManager->getStore()->getBaseUrl();
        $url_prefix = $this->getConfig('general_settings/url_prefix');
        $url_suffix = $this->getConfig('general_settings/url_suffix');
        $categoriesUrls = $this->getConfig('general_settings/categories_urls');
        $urlPrefix = '';
        if($url_prefix){
            $urlPrefix = $url_prefix.'/';
        }
        $categoryUrl = '';

        if($categoriesUrls){
            $category = $post->getData("categories");
            if($category && isset($category[0]['identifier']) && $category[0]['identifier']!=''){
                $categoryUrl = $category[0]['identifier'] . '/';
            }

        }
        return $url . $urlPrefix . $categoryUrl . $post->getIdentifier() . $url_suffix;
    }

    public function getSearchFormUrl(){
        $url = $this->_storeManager->getStore()->getBaseUrl();
        $url_prefix = $this->getConfig('general_settings/url_prefix');
        $url_suffix = $this->getConfig('general_settings/url_suffix');
        $urlPrefix = '';
        if($url_prefix){
            $urlPrefix = $url_prefix.'/';
        }
        return $url . $urlPrefix . 'search';
    }


    public function getPostCategories($postId){
        if($this->_postCategories == NULL){
            $connection = $this->_resource->getConnection();
            $select = 'SELECT * FROM ' . $this->_resource->getTableName('ves_blog_post_category') . ' WHERE post_id = ' . $postId . ' ORDER BY position ASC';
            $this->_postCategories = $connection->fetchAll($select);
        }
        return $this->_postCategories;
    }

    public function subString( $text, $length = 100, $replacer ='...', $is_striped=true ){
        if($length == 0) return $text;
        $text = ($is_striped==true)?strip_tags($text):$text;
        if(strlen($text) <= $length){
            return $text;
        }
        $text = substr($text,0,$length);
        $pos_space = strrpos($text,' ');
        return substr($text,0,$pos_space).$replacer;
    }

    /**
     * @param string|null $route
     * @param array|null $params
     * @return string
     */
    public function getUrl($route, $params = [])
    {
        return $this->_frontendUrlBuilder->getUrl($route, $params);
    }

    public function getPostCategoryUrl($post)
    {
        $category = '';
        $categories = $this->getPostCategories($post->getPostId());
        if(!empty($categories)){
            $connection = $this->_resource->getConnection();
            $select = 'SELECT * FROM ' . $this->_resource->getTableName('ves_blog_category') . ' WHERE category_id = ' . $categories[0]['category_id'];
            $category = $connection->fetchAll($select);
            return $category;
        }
        return $category;
    }

    /**
     * @param string $file
     * @return string
     */
    public function getMediaUrl($file)
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $file;
    }

    public function getPostByCategory($categoryId){
        $collection = $this->_postFactory->getCollection();
    }

    public function getCoreRegistry(){
        return $this->_coreRegistry;
    }

    public function getSearchKey(){
        return $this->_request->getParam('s');
    }
}