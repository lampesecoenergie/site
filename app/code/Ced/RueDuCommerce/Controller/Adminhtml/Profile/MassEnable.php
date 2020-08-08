<?php
namespace Ced\RueDuCommerce\Controller\Adminhtml\Profile;

class MassEnable extends \Magento\Backend\App\Action
{

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $profileIds = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded', false);
        if (!is_array($profileIds) && !$excluded) {
            $this->messageManager->addErrorMessage(__('Please select Profile(s).'));
        } elseif ($excluded == "false") {
            $profileIds  = $this->_objectManager->create('Ced\RueDuCommerce\Model\Profile')
                ->getCollection()->getAllIds();
        }

        if (!empty($profileIds)) {
            try {
                foreach ($profileIds as $profileId) {
                    $profile = $this->_objectManager->create('Ced\RueDuCommerce\Model\Profile')
                        ->load($profileId);
                    $profile->setProfileStatus(1);
                    $profile->save();
                }

                $this->messageManager->addSuccessMessage(
                    __('Total of %1 record(s) have been enabled.', count($profileIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $this->_redirect('*/*/index');
    }
}
