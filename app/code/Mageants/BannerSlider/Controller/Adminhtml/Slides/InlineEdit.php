<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Controller\Adminhtml\Slides;

use \Magento\Framework\Controller\Result\JsonFactory;
use \Mageants\BannerSlider\Model\SlidesFactory;
use \Magento\Backend\App\Action\Context;
		
abstract class InlineEdit extends \Magento\Backend\App\Action
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_BannerSlider::slide_new_edit';
    /**
     * JSON Factory
     * 
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_jsonFactory;

    /**
     * Sliders Factory
     * 
     * @var \Mageants\BannerSlider\Model\SlidersFactory
     */
    protected $_slidesFactory;

    /**
     * constructor
     * 
     * @param JsonFactory $jsonFactory
     * @param SlidersFactory $slidersFactory
     * @param Context $context
     */
    public function __construct(
        JsonFactory $jsonFactory,
        SlidesFactory $slidesFactory,
        Context $context
    )
    {
        $this->_jsonFactory = $jsonFactory;
		
        $this->_slidesFactory = $slidesFactory;
		
        parent::__construct($context);
    }
	
	  /*
	 * Check permission via ACL resource
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed(Self::RESOURCE_ID);
	}
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->_jsonFactory->create();

        $error = false;
		
        $messages = [];
		
        $slideItems = $this->getRequest()->getParam('items', []);
		
        if (!($this->getRequest()->getParam('isAjax') && count($slideItems))) 
		{
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }
		
        foreach (array_keys($slideItems) as $slideId) 
		{
            /** @var \Mageants\BannerSlider\Model\Sliders $slider */
            $slide = $this->_slidesFactory->create()->load($slideId);
			
            try 
			{
                $slideData = $slideItems[$slideId];//todo: handle dates
				
                $slide->addData($slideData);
				
                $slide->save();
				
            } 
			catch (\Magento\Framework\Exception\LocalizedException $e) 
			{
                $messages[] = $this->getErrorWithSlideId($slide, $e->getMessage());
				
                $error = true;
            } 
			catch (\RuntimeException $e) 
			{
                $messages[] = $this->getErrorWithSlideId($slide, $e->getMessage());
				
                $error = true;
            } 
			catch (\Exception $e) 
			{
                $messages[] = $this->getErrorWithSlideId(
                    $slide,
                    __('Something went wrong while saving the label.')
                );
				
                $error = true;
            }
        }
		
        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add Slider id to error message
     *
     * @param \Mageants\BannerSlder\Model\Sliders $slider
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithSlideId(\Mageants\BannerSlider\Model\Slides $slide, $errorText)
    {
        return '[Slide ID: ' . $slide->getId() . '] ' . $errorText;
    }
}
