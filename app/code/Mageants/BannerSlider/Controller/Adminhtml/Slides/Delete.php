<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Controller\Adminhtml\Slides;

class Delete extends \Mageants\BannerSlider\Controller\Adminhtml\Slides
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_BannerSlider::slide_delete';
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
                /** @var \Mageants\BannerSlider\Model\Slides $slide */
                $slide = $this->_slidesFactory->create();
				
                $slide->load($id);
				
				$name = $slide->getName();
				
                $slide->delete();
				
                $this->messageManager->addSuccess(__('The Slide has been deleted.'));
				
                $this->_eventManager->dispatch(
                    'adminhtml_mageants_bannerslider_slides_on_delete',
                    ['name' => $name, 'status' => 'success']
                );
				
				$sliderid = $this->getRequest()->getParam('sliderid');
				
				if($sliderid) 
				{
					$resultRedirect->setPath('mageants_bannerslider/sliders/edit/id/'.$sliderid.'/back/edit/active_tab/associated_slides/*/');
				}
				else
				{
					$resultRedirect->setPath('mageants_bannerslider/*/');
				}
				
                return $resultRedirect;
				
            } 
			catch (\Exception $e) 
			{
                $this->_eventManager->dispatch(
                    'adminhtml_mageants_bannerslider_slides_on_delete',
                    ['name' => $name, 'status' => 'fail']
                );
				
                // display error message
                $this->messageManager->addError($e->getMessage());
				
				$sliderid = $this->getRequest()->getParam('sliderid');
				
				if($sliderid) 
				{
					$resultRedirect->setPath('mageants_bannerslider/sliders/edit/id/'.$sliderid.'/back/edit/active_tab/associated_slides/*/');
				}
				else
				{
					// go back to edit form
					$resultRedirect->setPath('mageants_bannerslider/*/edit', ['id' => $id]);
				}
				
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
