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
namespace Ves\Blog\Block\Widget;

class Posts extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
	/**
	 * @var \Ves\Blog\Helper\Data
	 */
	protected $_blogHelper;

	/**
	 * @var \Ves\Blog\Model\Post
	 */
	protected $_post;

	protected $_resource;

	/**
	 * @param \Magento\Framework\View\Element\Template\Context
	 * @param \Ves\Blog\Helper\Data
	 * @param \Ves\Blog\Model\Post
	 * @param \Magento\Framework\App\ResourceConnection        $resource
	 * @param array
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Ves\Blog\Helper\Data $blogHelper,
		\Ves\Blog\Model\Post $post,
		\Magento\Cms\Model\Block $blockModel,
		\Magento\Framework\App\ResourceConnection $resource,
		array $data = []
		) {
		$this->_blogHelper = $blogHelper;
		$this->_post = $post;
		$this->_blockModel = $blockModel;
		$this->_resource = $resource;
		parent::__construct($context, $data);
	}

	 public function getCmsBlockModel(){
        return $this->_blockModel;
    }

	public function _toHtml(){
		$this->setTemplate("Ves_Blog::widget/posts.phtml");
		$itemPerPage = $this->getConfig('number_post', 6);
		$categories = $this->getConfig('categories');
		$categories = explode(",", $categories);

		$template = $this->getConfig('block_template');
		if ($template) {
            $this->setTemplate($template);
        }

		$store = $this->_storeManager->getStore();
		$collection = $this->_post
		->getCollection()
		->addFieldToFilter("is_active", 1)
		->setPagesize($itemPerPage)
		->addStoreFilter($store)
		->setCurpage(1);

		$collection->getSelect()
        ->joinLeft(
            [
            'cat' => $this->_resource->getTableName('ves_blog_post_category')],
            'cat.post_id = main_table.post_id',
            [
            'post_id' => 'post_id',
            'position' => 'position'
            ]
            )
        ->where('cat.category_id in (?)', $categories)
        ->limit($itemPerPage)
        ->group('main_table.post_id');

		$orderBy = $this->getConfig("orderby");
		if($orderBy == 1){
			$collection->getSelect()->order("main_table.post_id DESC");
		}else if($orderBy == 2){
			$collection->getSelect()->order("main_table.post_id ASC");
		}else if($orderBy == 3){
			$collection->getSelect()->order("main_table.hits DESC");
		}else if($orderBy == 4){
			$collection->getSelect()->order("main_table.hits ASC");
		}
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
		if($this->hasData($key)){
			return $this->getData($key);
		}
		return $default;
	}
}