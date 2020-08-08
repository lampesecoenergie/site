<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @category  Ced
 * @package   Ced_RueDuCommerce
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RueDuCommerce\Controller\Adminhtml\Profile;
 
class Delete extends \Magento\Customer\Controller\Adminhtml\Group
{
    protected $_objectManager;
    
    protected $_session;

    /**
     * Delete the Attribute
     */
    public function execute()
    {
        $code = $this->getRequest()->getParam('pcode');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($code) {
            $model = $this->_objectManager->create('Ced\RueDuCommerce\Model\Profile')->getCollection()->addFieldToFilter('profile_code', $code);

            // entity type check
            try {
                foreach ($model as $value) {
                    if ($code == $value->getData('profile_code')) {
                        $value->delete();
                    }
                }
                $this->messageManager->addSuccessMessage(__('You deleted the profile.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath(
                    'rueducommerce/profile/edit',
                    ['pcode' => $this->getRequest()->getParam('pcode')]
                );
                //End
            }
        }
        return $this->_redirect('rueducommerce/profile/index');
    }
}
