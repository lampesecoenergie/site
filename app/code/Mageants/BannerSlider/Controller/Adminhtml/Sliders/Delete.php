<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Controller\Adminhtml\Sliders;

class Delete extends \Mageants\BannerSlider\Controller\Adminhtml\Sliders
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_BannerSlider::slider_delete';
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->_resultRedirectFactory->create();
		
        $id = $this->getRequest()->getParam('id');
		
        if ($id) 
		{
            $name = "";
			
            try 
			{
                /** @var \Mageants\BannerSlider\Model\Sliders $slider */
                $slider = $this->_slidersFactory->create();
				
                $slider->load($id);
				
                $name = $slider->getName();
				
                $slider->delete();
				
                $this->messageManager->addSuccess(__('The Slider has been deleted.'));
				
                $this->_eventManager->dispatch(
                    'adminhtml_mageants_bannerslider_slider_on_delete',
                    ['name' => $name, 'status' => 'success']
                );
				
                $resultRedirect->setPath('mageants_bannerslider/*/');
				
                return $resultRedirect;
				
            } 
			catch (\Exception $e) 
			{
                $this->_eventManager->dispatch(
                    'adminhtml_mageants_bannerslider_label_on_delete',
                    ['name' => $name, 'status' => 'fail']
                );
				
                // display error message
                $this->messageManager->addError($e->getMessage());
				
                // go back to edit form
                $resultRedirect->setPath('mageants_bannerslider/*/edit', ['id' => $id]);
				
                return $resultRedirect;
            }
        }
		
        // display error message
        $this->messageManager->addError(__('Slider to delete was not found.'));
		
        // go to grid
        $resultRedirect->setPath('mageants_bannerslider/*/');
		
        return $resultRedirect;
    }
	 /*
	 * Check permission via ACL resource
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed(Self::RESOURCE_ID);
	}
}
