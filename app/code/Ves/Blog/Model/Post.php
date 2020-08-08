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
namespace Ves\Blog\Model;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Post extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Blog's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /**
     * URL Model instance
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_blogHelper;

    protected $_resource;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\Model\Context                          $context                  
     * @param \Magento\Framework\Registry                               $registry                 
     * @param \Magento\Store\Model\StoreManagerInterface                $storeManager             
     * @param \Ves\Blog\Model\ResourceModel\Blog|null                      $resource                 
     * @param \Ves\Blog\Model\ResourceModel\Blog\Collection|null           $resourceCollection       
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory 
     * @param \Magento\Store\Model\StoreManagerInterface                $storeManager             
     * @param \Magento\Framework\UrlInterface                           $url                      
     * @param \Ves\Blog\Helper\Data                                    $brandHelper              
     * @param array                                                     $data                     
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Ves\Blog\Model\ResourceModel\Post $resource = null,
        \Ves\Blog\Model\ResourceModel\Post\Collection $resourceCollection = null,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $url,
         \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
        ) {
        $this->_storeManager = $storeManager;
        $this->_url = $url;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_resource = $resource;
        $this->scopeConfig = $scopeConfig;
    }

	/**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ves\Blog\Model\ResourceModel\Post');
    }

    /**
     * Prepare page's statuses.
     * Available event cms_page_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    public function getPostTags()
    {
        $connection = $this->_resource->getConnection();
        $select = 'SELECT * FROM ' . $this->_resource->getTable('ves_blog_post_tag') . ' WHERE post_id = "' . $this->getData("post_id") . '"';
        $tags = $connection->fetchAll($select);
        return $tags;
    }

    public function getPostCategories(){
        $connection = $this->_resource->getConnection();
        $select = 'SELECT * FROM ' . $this->_resource->getTable('ves_blog_post_category');
        $categories = $connection->fetchAll($select);
        $tmp = [];
        foreach ($categories as $k => $v) {
            if($v['post_id'] == $this->getData("post_id")){
                $select = 'SELECT * FROM ' . $this->_resource->getTable('ves_blog_category') . ' WHERE category_id = ' . $v['category_id'];
                $select = $connection->select()->from(['ves_blog_category' => $this->_resource->getTable('ves_blog_category')])
                ->where('ves_blog_category.category_id = ' . (int)$v['category_id'])
                ->order('ves_blog_category.cat_position DESC');
                $category = $connection->fetchRow($select);
                $tmp[] = $category;
                unset($categories[$k]);
            }
        }
        return $tmp;
    }

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

    public function getUrl()
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
            $category = $this->getPostCategories();
            if($category && isset($category[0]['identifier']) && $category[0]['identifier']!=''){
                $categoryUrl = $category[0]['identifier'] . '/';
            }

        }
        return $url . $urlPrefix . $categoryUrl . $this->getIdentifier() . $url_suffix;
    }
    
}