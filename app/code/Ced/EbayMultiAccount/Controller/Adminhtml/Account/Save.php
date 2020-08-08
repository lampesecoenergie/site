<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_EbayMultiAccount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Controller\Adminhtml\Account;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;

/**
 * Class Save
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\Account
 */
class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_EbayMultiAccount::EbayMultiAccount';

    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * Save constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Ced\EbayMultiAccount\Helper\Cache $cache
     */
    public function __construct(
        Context $context,
        \Ced\EbayMultiAccount\Model\AccountsFactory $accounts,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper
    )
    {
        parent::__construct($context);
        $this->accounts = $accounts;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    public function execute()
    {
        $accountDetails = $this->getRequest()->getParams();
        try {
            if (isset($accountDetails['account_code']) || isset($accountDetails['id'])) {
                if (isset($accountDetails['id'])) {
                    $accounts = $this->accounts->create()->load($accountDetails['id']);
                } else {
                    $accounts = $this->accounts->create();
                }
                $accounts->addData($accountDetails)->save();
                $this->multiAccountHelper->createProfileAttribute($accounts->getId(), $accounts->getAccountCode());
                $this->messageManager->addSuccessMessage(__('Account Saved Successfully.'));
                $this->_redirect('*/*/edit', ['id' => $accounts->getId()]);
            } else {
                $this->messageManager->addNoticeMessage(__('Please fill the Account Code'));
                $this->_redirect('*/*/new');
            }
        } catch (\Exception $e) {
            $this->_objectManager->create('Ced\EbayMultiAccount\Helper\Logger')->addError('In Save Account: ' . $e->getMessage(), ['path' => __METHOD__]);
            $this->messageManager->addErrorMessage(__('Unable to Save Account Details Please Try Again.' . $e->getMessage()));
            $this->_redirect('*/*/new');
        }
        return;
    }
}