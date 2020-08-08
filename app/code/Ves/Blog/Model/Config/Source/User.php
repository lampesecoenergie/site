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

use Magento\Framework\Data\OptionSourceInterface;

class User implements OptionSourceInterface
{
    /**
     * @var \Magento\User\Model\UserFactory
     */
	protected $_userFactory;

	public function __construct(
		\Magento\User\Model\UserFactory $userFactory
		){
		$this->_userFactory = $userFactory;
	}

    public function toOptionArray()
    {
    	$options = [];
        $collection = $this->_userFactory->create()->getCollection();
        foreach ($collection as $_user) {
        	$options[$_user->getUserId()] = $_user->getFirstname() . ' ' . $_user->getLastname();
        }
        return $options;
    }
}