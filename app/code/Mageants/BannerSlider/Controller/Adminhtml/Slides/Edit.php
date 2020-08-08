<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Controller\Adminhtml\Slides;

use \Magento\Backend\Model\Session;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Mageants\BannerSlider\Model\SlidesFactory;
use \Magento\Framework\Registry;
use \Magento\Backend\Model\View\Result\RedirectFactory;
use \Magento\Backend\App\Action\Context;

class Edit extends \Mageants\BannerSlider\Controller\Adminhtml\Slides
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_BannerSlider::slide_new_edit';
    /**
     * Backend session
     * 
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * Page factory
     * 
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Result JSON factory
     * 
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * constructor
     * 
     * @param Session $backendSession
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param SlidersFactory $slidersFactory
     * @param Registry $registry
     * @param RedirectFactory $resultRedirectFactory
     * @param Context $context
     */
    public function __construct(
        
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        SlidesFactory $slidesFactory,
        Registry $registry,        
        Context $context
    )
    {
		$this->_backendSession = $context->getSession();
		
        $this->_resultPageFactory = $resultPageFactory;
		
        $this->_resultJsonFactory = $resultJsonFactory;
		
        parent::__construct($slidesFactory, $registry, $context);
    }

    /*
	 * Check permission via ACL resource
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed(Self::RESOURCE_ID);
	}

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
	
       $id = $this->getRequest()->getParam('id');
	   
       $sliderid = $this->getRequest()->getParam('sliderid');
	 
        /** @var \Mageants\BannerSlider\Model\Slides $slide */
        $slide = $this->_initSlides();
		
        /** @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
		  
        $resultPage->setActiveMenu('Mageants_BannerSlider::slides');
	
        $resultPage->getConfig()->getTitle()->set(__('Slides'));
			
        if ($id) 
		{
            $slide->load($id);
			
            if (!$slide->getId()) 
			{
                $this->messageManager->addError(__('This Slide no longer exists.'));
				
                $resultRedirect = $this->_resultRedirectFactory->create();
				
                $resultRedirect->setPath(
                    'mageants_bannerslider/*/edit',
                    [
                        'id' => $slide->getId(),
                        '_current' => true
                    ]
                );
				
                return $resultRedirect;
            }
        }
		
        $title = $slide->getId() ? $slide->getLabel() : __('New Slide');
		
        $resultPage->getConfig()->getTitle()->prepend($title);
		
        $data = $this->_backendSession->getData('mageants_bannerslider_slides_data', true);
		
        if (!empty($data)) 
		{
            $slide->setData($data);
        }
		
        return $resultPage;
    }
}
