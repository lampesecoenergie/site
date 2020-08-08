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
namespace Ves\Blog\Model\Config\Source;

class Categories implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\User\Model\UserFactory
     */
	protected $_categoryFactory;

    /**
     * @param \Ves\Blog\Model\Category
     */
	public function __construct(
		\Ves\Blog\Model\Category $categoryFactory
		){
		$this->_categoryFactory = $categoryFactory;
	}

    public function toOptionArray()
    {
    	$options = [];
        $collection = $this->_categoryFactory->getCollection();
        foreach ($collection as $_cat) {
        	$options[] = [
                'label' => $_cat->getName(),
                'value' => $_cat->getCategoryId()
            ];
        }
        return $options;
    }
}