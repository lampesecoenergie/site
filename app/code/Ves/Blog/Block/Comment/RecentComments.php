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
namespace Ves\Blog\Block\Comment;

class RecentComments extends \Magento\Framework\View\Element\Template
{
	/**
     * Product Collection
     *
     * @var AbstractCollection
     */
    protected $_postCollection;

    /**
     * @var \Ves\Blog\Helper\Data
     */
    protected $_blogHelper;

    /**
     * @var \Ves\Blog\Model\Comment
     */
    protected $_comment;

    /**
     * @var \Ves\Blog\Model\Post
     */
    protected $_post;

    /**
     * @var Ves\Blog\Model\ResourceModel\Category\Collection
     */
    protected $_colleciton;

    /**
     * @param \Magento\Framework\View\Element\Template\Context
     * @param \Ves\Blog\Helper\Data
     * @param \Ves\Blog\Model\Comment
     * @param \Ves\Blog\Model\Post
     * @param array
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ves\Blog\Helper\Data $blogHelper,
        \Ves\Blog\Model\Comment $comment,
        \Ves\Blog\Model\Post $post,
        array $data = []
        ) {
        parent::__construct($context, $data);
        $this->_blogHelper = $blogHelper;
        $this->_comment = $comment;
        $this->_post = $post;
    }

    public function _toHtml(){
        $store = $this->_storeManager->getStore();
        $itemPerPage = $this->getConfig('item_per_page', 6);
        $collection = $this->_comment
        ->getCollection()
        ->setPagesize($itemPerPage)
        ->addFieldToFilter("main_table.is_active", 1)
        ->addStoreFilter($store)
        ->setOrder("comment_id", "DESC");
        $postIds = [];
        foreach ($collection as $_comment) {
        	$postIds[] = $_comment->getPostId();
        }
        $postIds = array_unique($postIds);
        $postCollection = $this->_post->getCollection();
        $postCollection->getSelect()->where("is_active = 1")
        ->where("post_id IN (?)", $postIds);
        $posts = [];
        foreach ($postCollection as $_post) {
        	$posts[$_post->getPostId()] = $_post;
        }
        $this->setData("posts", $posts);
        $this->setCollection($collection); 
        return parent::_toHtml();
    }

	/**
     * @param AbstractCollection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->_postCollection = $collection;
        return $this;
    }

    public function getCollection()
    {
    	return $this->_postCollection;
    }

    public function getConfig($key, $default = '')
    {   
        $c = explode("/", $key);
        if(count($c)==2){
            if($this->hasData($c[1])){
                return $this->getData($c[1]);
            }
        }
        if($this->hasData($key)){
            return $this->getData($key);
        }
        return $default;
    }
}