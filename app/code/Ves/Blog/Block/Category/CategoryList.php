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

class CategoryList extends \Magento\Framework\View\Element\Template
{
	/**
	 * @var \Ves\Blog\Helper\Data
	 */
	protected $_blogHelper;

	/**
	 * @var \Ves\Blog\Model\Category
	 */
	protected $_tag;

	/**
	 * @var Ves\Blog\Model\ResourceModel\Category\Collection
	 */
	protected $_colleciton;

	/**
	 * @param \Magento\Framework\View\Element\Template\Context
	 * @param \Ves\Blog\Helper\Data
	 * @param \Ves\Blog\Model\Category
	 * @param array
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Ves\Blog\Helper\Data $blogHelper,
		\Ves\Blog\Model\Category $category,
		array $data = []
		) {
		parent::__construct($context, $data);
		$this->_blogHelper = $blogHelper;
		$this->_category = $category;
	}

	public function _toHtml(){
		if(!$this->_blogHelper->getConfig('general_settings/enable')) return;
		$store = $this->_storeManager->getStore();
		$collection = $this->_category->getCollection()
		->addFieldToFilter('is_active', 1)
		->addStoreFilter($store)
		->setOrder("cat_position", "ASC");
		$this->setCollection($collection);
		return parent::_toHtml();
	}

	public function setCollection($collection){
		$this->_collection = $collection;
		return $this;
	}

	public function getCollection(){
		return $this->_collection;
	}
}