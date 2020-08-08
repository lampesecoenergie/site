<?php
namespace Ced\RueDuCommerce\Controller\Adminhtml\Profile;

class MassDisable extends \Magento\Backend\App\Action
{

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $profileIdsToDisable = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded', false);
        if (!is_array($profileIdsToDisable) && !$excluded) {
            
            $this->messageManager->addErrorMessage(__('Please select Profile(s).'));
        } elseif ($excluded == "false") {
           
            $profileIdsToDisable  = $this->_objectManager->create('Ced\RueDuCommerce\Model\Profile')
                ->getCollection()->getAllIds();
        }

        if (!empty($profileIdsToDisable)) {
            try {
                foreach ($profileIdsToDisable as $profileId) {
                    $profile = $this->_objectManager->create('Ced\RueDuCommerce\Model\Profile')
                        ->load($profileId);
                    $profile->setProfileStatus(0);
                    $profile->save();
                }
                $this->messageManager->addSuccessMessage(__('Total of %1 record(s) have been disabled.', count($profileIdsToDisable)));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $this->_redirect('*/*/index');
    }
}
