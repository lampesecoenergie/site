<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Controller\Adminhtml\Sliders;

use \Magento\Framework\Controller\Result\JsonFactory;
use \Mageants\BannerSlider\Model\SlidersFactory;
use \Magento\Backend\App\Action\Context;

abstract class InlineEdit extends \Magento\Backend\App\Action
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_BannerSlider::slider_new_edit';
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
    protected $_slidersFactory;

    /**
     * constructor
     * 
     * @param JsonFactory $jsonFactory
     * @param SlidersFactory $slidersFactory
     * @param Context $context
     */
    public function __construct(
        JsonFactory $jsonFactory,
        SlidersFactory $slidersFactory,
        Context $context
    )
    {
        $this->_jsonFactory = $jsonFactory;
		
        $this->_slidersFactory = $slidersFactory;
		
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
		
        $sliderItems = $this->getRequest()->getParam('items', []);
		
        if (!($this->getRequest()->getParam('isAjax') && count($sliderItems))) 
		{
            return $resultJson->setData(
				[
					'messages' => [__('Please correct the data sent.')],
					'error' => true,
				]
			);
        }
		
        foreach (array_keys($sliderItems) as $sliderId) 
		{
            /** @var \Mageants\BannerSlider\Model\Sliders $slider */
            $slider = $this->_slidersFactory->create()->load($sliderId);
			
            try 
			{
                $sliderData = $sliderItems[$sliderId];//todo: handle dates
				
                $slider->addData($sliderData);
				
                $slider->save();
				
            } 
			catch (\Magento\Framework\Exception\LocalizedException $e) 
			{
                $messages[] = $this->getErrorWithSliderId($slider, $e->getMessage());
				
                $error = true;
				
            } 
			catch (\RuntimeException $e) 
			{
                $messages[] = $this->getErrorWithSliderId($slider, $e->getMessage());
				
                $error = true;
				
            } 
			catch (\Exception $e) 
			{
                $messages[] = $this->getErrorWithSliderId(
                    $slider,
                    __('Something went wrong while saving the label.')
                );
				
                $error = true;
            }
        }
		
        return $resultJson->setData(
			[
				'messages' => $messages,
				'error' => $error
			]
		);
    }

    /**
     * Add Slider id to error message
     *
     * @param \Mageants\BannerSlder\Model\Sliders $slider
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithSliderId(\Mageants\BannerSlider\Model\Sliders $slider, $errorText)
    {
        return '[Slider ID: ' . $slider->getId() . '] ' . $errorText;
    }
}
