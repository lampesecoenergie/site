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
namespace Ves\Blog\Block\Tag;

class TagList extends \Magento\Framework\View\Element\Template
{
	/**
	 * @var \Ves\Blog\Helper\Data
	 */
	protected $_blogHelper;

	/**
	 * @var \Ves\Blog\Model\Tag
	 */
	protected $_tag;

	/**
	 * @var Ves\Blog\Model\ResourceModel\Tag\Collection
	 */
	protected $_colleciton;

	/**
	 * @param \Magento\Framework\View\Element\Template\Context
	 * @param \Ves\Blog\Helper\Data
	 * @param \Ves\Blog\Model\Tag
	 * @param array
	 */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ves\Blog\Helper\Data $blogHelper,
        \Ves\Blog\Model\Tag $tag,
        array $data = []
        ) {
        parent::__construct($context, $data);
        $this->_blogHelper = $blogHelper;
        $this->_tag = $tag;
    }

    public function _toHtml(){
        if(!$this->_blogHelper->getConfig('general_settings/enable')){
            return;
        }
        $collection = $this->_tag->getCollection();
        $tags = [];
		foreach ($collection as $k => $v) {
			$count = 1;
			if(isset($tags[$v['alias']])){
				$count = $tags[$v['alias']]['count']+1;
			}
			$tags[$v['alias']] = [
				'name' => $v['name'],
				'count' => $count
			];
		}
		$this->setData("tags", $tags);
        return parent::_toHtml();
    }
}