<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_EbayMultiAccount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Controller\Adminhtml\Profile;

class MassDisable extends \Magento\Backend\App\Action
{

    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_EbayMultiAccount::EbayMultiAccount';
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $proIds = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded', false);
        if (!is_array($proIds) && !$excluded) {
            $this->messageManager->addErrorMessage(__('Please select Profile(s).'));
        } else if($excluded == "false") {
            $proIds  = $this->_objectManager->create('Ced\EbayMultiAccount\Model\Profile')->getCollection()->getAllIds();
        }

        if (!empty($proIds)) {
            try {
                foreach ($proIds as $profileId) {
                    $profile = $this->_objectManager->create('Ced\EbayMultiAccount\Model\Profile')->load($profileId);
                    $profile->setProfileStatus(0);
                    $profile->getResource()->save($profile);
                }
                $this->messageManager->addSuccessMessage(__('Total of %1 record(s) have been disabled.', count($proIds)));
            } catch (\Exception $e) {
                $this->_objectManager->create('Ced\EbayMultiAccount\Helper\Logger')->addError('In Mass Disable Profile: '.$e->getMessage(), ['path' => __METHOD__]);
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}