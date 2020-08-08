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
 * @category    Ced
 * @package     Ced_EbayMultiAccount
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class NewAction
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\Profile
 */
class NewAction extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ced_EbayMultiAccount::EbayMultiAccount';
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * NewAction constructor.
     * @param Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->scopeConfigManager = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
    }

    public function execute()
    {
        $accounts = $this->multiAccountHelper->getAllAccounts();
        //$totalAccounts = $accounts->getSize();
        $accountID = $this->getRequest()->getParam('account_id');

        if(!$accountID) {
            $accountID = $this->scopeConfigManager->getValue('ebaymultiaccount_config/ebaymultiaccount_setting/primary_account');
        }
        /** @var \Ced\EbayMultiAccount\Model\Accounts $account */
        $account = $this->multiAccountHelper->getAccountRegistry($accountID);

        if ($accountID && $accountID != '' && $account->getId()/* || $totalAccounts == 1*/) {
            return $this->resultForwardFactory->create()->forward('edit');
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_EbayMultiAccount::EbayMultiAccount');
        $resultPage->getConfig()->getTitle()->prepend(__('Profiles'));
        $resultPage->getConfig()->getTitle()->prepend(__('Select Account'));
        return $resultPage;
    }
}
