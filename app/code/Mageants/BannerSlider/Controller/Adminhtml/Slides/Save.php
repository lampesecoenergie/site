<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Controller\Adminhtml\Slides;

use \Magento\Backend\Model\Session;
use \Mageants\BannerSlider\Model\SlidesFactory;
use \Magento\Framework\Registry;
use \Magento\Backend\Model\View\Result\RedirectFactory;
use \Magento\Backend\App\Action\Context;
use \Mageants\BannerSlider\Model\Upload;
use \Mageants\BannerSlider\Model\ResourceModel\Image;
use \Mageants\BannerSlider\Helper\Data;

class Save extends \Mageants\BannerSlider\Controller\Adminhtml\Slides
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_BannerSlider::slide_save';
    /**
     * Upload model
     * 
     * @var \Mageants\BannerSlider\Model\Upload
     */
    protected $_uploadModel;

    /**
     * Image model
     * 
     * @var \Mageants\BannerSlider\Model\Slide\Image
     */
    protected $_imageModel;

    /**
     * Backend session
     * 
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;
	
	/**
     * Backend session
     * 
     * @var \Mageants\BannerSlider\Helper\Data
     */
    protected $_bannerHelper;

    /**
     * constructor
     * 
     * @param Upload $uploadModel
     * @param File $fileModel
     * @param Image $imageModel
     * @param Session $backendSession
     * @param SlidersFactory $slidersFactory
     * @param Registry $registry
     * @param RedirectFactory $resultRedirectFactory
     * @param Context $context
     */
    public function __construct(
        
        SlidesFactory $slidersFactory,
        Registry $registry,
        
        Context $context,
		Upload $uploadModel,
		Image $imageModel,
		Data $bannerHelper
    )
    {
		
        $this->_backendSession = $context->getSession();
		
        $this->_uploadModel = $uploadModel;
		
        $this->_imageModel = $imageModel;
		
        $this->_bannerHelper = $bannerHelper;
		
        parent::__construct($slidersFactory, $registry, $context);
    }
	  /*
	 * Check permission via ACL resource
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed(Self::RESOURCE_ID);
	}

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('slide');
		
        $slidesetting = $this->getRequest()->getPost('slidesetting');
		
		$data['product_ids'] = str_replace('&', ', ',$data['product_ids']);
		
		$data['slidesetting'] =   $this->_bannerHelper->serializeSetting($slidesetting);
		
        $resultRedirect = $this->resultRedirectFactory->create();
	
        if ($data) 
		{
            $slide = $this->_initSlides();
			
            $slide->setData($data);
			
			$image = $this->_uploadModel->uploadFileAndGetName('image', $this->_imageModel->getBaseDir(), $data);
			
            $slide->setImage($image);
            
			$this->_eventManager->dispatch(
                'mageants_bannerslider_slides_prepare_save',
                [
                    'slider' => $slide,
                    'request' => $this->getRequest()
                ]
            );
			
            try 
			{
                $slide->save();
				
				$sliderid = $slide->getSliderId();
				
                $this->messageManager->addSuccess(__('The Slide has been saved.'));
				
                $this->_backendSession->setMageantsBannerSliderSlideData(false);				
				
				$sliderid = $this->getRequest()->getParam('sliderid');
				
				if($sliderid) 
				{
					    if ($this->getRequest()->getParam('back')) 
						{
							$resultRedirect->setPath(
								'mageants_bannerslider/sliders/edit/',
								[
									'id' => $sliderid,
									'_current' => true
								]
							);
							
							return $resultRedirect;
						}
						
						$resultRedirect->setPath('mageants_bannerslider/sliders/edit/id/'.$sliderid.'/back/edit/active_tab/associated_slides/*/');
				}
				else
				{
					if ($this->getRequest()->getParam('back')) 
					{
						$resultRedirect->setPath(
							'mageants_bannerslider/*/edit',
							[
								'id' => $slide->getId(),
								'_current' => true
							]
						);
						
						return $resultRedirect;
					}
					
					$resultRedirect->setPath('mageants_bannerslider/*/');
				}
				
                return $resultRedirect;
				
            } 
			catch (\Magento\Framework\Exception\LocalizedException $e) 
			{
                $this->messageManager->addError($e->getMessage());
            } 
			catch (\RuntimeException $e) 
			{
                $this->messageManager->addError($e->getMessage());
            } 
			catch (\Exception $e) 
			{
                $this->messageManager->addException($e, __('Something went wrong while saving the Slide.'));
            }
			
            $this->_getSession()->setMageantsBannerSliderPostData($data);
			
            $resultRedirect->setPath(
                'mageants_bannerslider/*/edit',
                [
                    'id' => $slide->getId(),
                    '_current' => true
                ]
            );
			
            return $resultRedirect;
        }
		
        $resultRedirect->setPath('mageants_bannerslider/*/');
		
        return $resultRedirect;
    }
	
}
