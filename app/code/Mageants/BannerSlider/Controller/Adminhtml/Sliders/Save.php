<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Controller\Adminhtml\Sliders;

use \Magento\Backend\Model\Session;
use \Mageants\BannerSlider\Model\SlidersFactory;
use \Mageants\BannerSlider\Model\SlidesFactory;
use \Magento\Framework\Registry;
use \Magento\Backend\Model\View\Result\RedirectFactory;
use \Magento\Backend\App\Action\Context;
use \Mageants\BannerSlider\Helper\Data;
		
class Save extends \Mageants\BannerSlider\Controller\Adminhtml\Sliders
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_BannerSlider::slider_save';
    /**
     * Upload model
     * 
     * @var \Mageants\BannerSlider\Model\SlidesFactory
     */
    protected $_slidesFactory;

    /**
     * File model
     * 
     * @var \Mageants\BannerSlider\Model\Slides\File
     */
    protected $_fileModel;

    /**
     * Image model
     * 
     * @var \Mageants\BannerSlider\Model\Slides\Image
     */
    protected $_imageModel;

    /**
     * Backend session
     * 
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;
	
    /**
     * Banner Data Helper
     * 
     * @var \Magento\Backend\Model\Session
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
        SlidersFactory $slidersFactory,
        SlidesFactory $slidesFactory,
        Registry $registry,
        Context $context,
		Data $bannerHelper
    )
    {
        $this->_backendSession = $context->getSession();
		
		$this->_bannerHelper = $bannerHelper;
		
		$this->_slidesFactory = $slidesFactory;
		
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
        $data = $this->getRequest()->getPost('slider');
		
        $resultRedirect = $this->resultRedirectFactory->create();
		
        if ($data) 
		{
            $slider = $this->_initSliders();
			
			$slidersetting = $this->getRequest()->getPost('slidersetting');
			
			$slidepositions = $this->getRequest()->getPost('slidepositions');
			
			$data['setting'] =   $this->_bannerHelper->serializeSetting($slidersetting);
			
            $slider->setData($data);
			
            $this->_eventManager->dispatch(
                'mageants_bannerslider_slider_prepare_save',
                [
                    'slider' => $slider,
                    'request' => $this->getRequest()
                ]
            );
			
            try 
			{
                $slider->save();
				
				$slidesFactory = $this->_slidesFactory->create();
				
				if(is_array($slidepositions))
				{
					foreach($slidepositions as $slide_id => $position)
					{
						$slide_ids[] = $slide_id;						
					}
					
					$slidesCollection = $slidesFactory->getCollection();
					
					$slidesCollection->addFieldToFilter('slider_id', array('in' => $slider->getId()));							
					
					foreach ($slidesCollection as $slide)
					{
							$slide->setPosition($slidepositions[ $slide->getId() ]);
							$slide->save();
					}
				}
				
                $this->messageManager->addSuccess(__('The Slider has been saved.'));
				
                $this->_backendSession->setMageantsBannerSlidersData(false);
				
                if ($this->getRequest()->getParam('back')) 
				{
                    $resultRedirect->setPath(
                        'mageants_bannerslider/*/edit',
                        [
                            'id' => $slider->getId(),
                            '_current' => true
                        ]
                    );
					
                    return $resultRedirect;
                }
				
                $resultRedirect->setPath('mageants_bannerslider/*/');
				
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
                $this->messageManager->addException($e, __('Something went wrong while saving the Slider.'));
            }
			
            $this->_getSession()->setMageantsBannerSliderPostData($data);
			
            $resultRedirect->setPath(
                'mageants_bannerslider/*/edit',
                [
                    'id' => $slider->getId(),
                    '_current' => true
                ]
            );
			
            return $resultRedirect;
        }
		
        $resultRedirect->setPath('mageants_bannerslider/*/');
		
        return $resultRedirect;
    }
	
}
