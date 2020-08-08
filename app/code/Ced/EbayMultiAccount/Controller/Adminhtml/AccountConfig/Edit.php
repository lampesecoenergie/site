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

namespace Ced\EbayMultiAccount\Controller\Adminhtml\AccountConfig;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Ced\EbayMultiAccount\Model\AccountConfigFactory;

/**
 * Class Edit
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\Account
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    const ADMIN_RESOURCE = 'Ced_EbayMultiAccount::EbayMultiAccount';
    
    /**
     * @var \Ced\EbayMultiAccount\Model\AccountConfigFactory
     */
    public $accountconfig;

    /**
     * Edit constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param AccountConfigFactory $accountconfig
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        AccountConfigFactory $accountconfig,
        \Ced\EbayMultiAccount\Helper\Data $dataHelper,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper,
        \Magento\Framework\Registry $coreRegistry
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->accountconfig = $accountconfig;
        $this->_coreRegistry = $coreRegistry;
        $this->dataHelper = $dataHelper;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $accountconfig = $this->accountconfig->create()->load($id);
            $this->multiAccountHelper->getAccountRegistry($accountconfig->getAccountId());
            $this->dataHelper->updateAccountVariable();
        } else {
            $accountconfig = $this->accountconfig->create();
        }

        $this->_coreRegistry->register('current_accountconfig', $accountconfig);    
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend($accountconfig->getId() ? $accountconfig->getConfigName() : __('New Account Configuration'));
        return $resultPage;
    }
}