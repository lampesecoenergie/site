<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Controller\Adminhtml\Sliders;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\LayoutFactory;

class Slides extends \Magento\Backend\App\Action
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_BannerSlider::slider_new_edit';
	/**
	 * 
	 * @var  \Magento\Framework\View\Result\LayoutFactory
     */
	protected $_resultLayoutFactory = null;
	
	  /**
     * constructor
     * 
	 * @param Context $context
     * @param LayoutFactory $resultLayoutFactory
     */
	public function __construct(
		Context $context,
		LayoutFactory $resultLayoutFactory
	) 
	{
		parent::__construct($context);
		
		$this->_resultLayoutFactory = $resultLayoutFactory;
	}
	
	 /**
     * execute action
     *
     * @return layout result
     */
	public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();
		
		$resultLayout->getLayout()->getBlock('bannerslider.sliders.edit.tab.slides');

        return $resultLayout;
    }
	
	  /*
	 * Check permission via ACL resource
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed(Self::RESOURCE_ID);
	}

}