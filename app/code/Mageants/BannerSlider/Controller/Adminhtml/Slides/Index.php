<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Controller\Adminhtml\Slides;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
		
class Index extends \Magento\Backend\App\Action
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_BannerSlider::slide_grid';
	/**
     * Page factory
     * 
     * @var \Magento\Framework\View\Result\PageFactory
     */
	protected $_resultPageFactory = null;
	
	/**
     * Page factory
     * 
     * @var _resultPage
     */
	protected $_resultPage = null;
	
	/**
     * constructor
     * 
     * @param RedirectFactory $resultRedirectFactory
     * @param Context $context
     */
	public function __construct(
		Context $context,
		PageFactory $resultPageFactory
	) 
	{
		parent::__construct($context);
		
		$this->_resultPageFactory = $resultPageFactory;
	}

	public function execute()
	{
		//Call page factory to render layout and page content
		$this->_setPageData();
		
        return $this->getResultPage();
	}

	  /*
	 * Check permission via ACL resource
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed(Self::RESOURCE_ID);
	}

	/*
	 * return  result page
	 */
    public function getResultPage()
    {
        if (is_null($this->_resultPage)) 
		{
            $this->_resultPage = $this->_resultPageFactory->create();
        }
		
        return $this->_resultPage;
    }

	/*
	 * set page data and active menu 
	 *
	 * return $this 
	 */
    protected function _setPageData()
    {
        $resultPage = $this->getResultPage();
		
        $resultPage->setActiveMenu('Mageants_BannerSlider::slides');
		
        $resultPage->getConfig()->getTitle()->prepend((__('Slides')));

        //Add bread crumb
        $resultPage->addBreadcrumb(__('Mageants'), __('Mageants'));
		
        $resultPage->addBreadcrumb(__('Banner Slides'), __('Manage Slides'));

        return $this;
    }


}