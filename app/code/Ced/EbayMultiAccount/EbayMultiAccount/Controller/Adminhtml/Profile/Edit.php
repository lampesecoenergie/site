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

namespace Ced\EbayMultiAccount\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Edit
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\Profile
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var
     */
    protected $_entityTypeId;

    const ADMIN_RESOURCE = 'Ced_EbayMultiAccount::EbayMultiAccount';
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var \Ced\EbayMultiAccount\Helper\Data
     */
    protected $dataHelper;

    /**
     * Edit constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        PageFactory $resultPageFactory,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper,
        \Ced\EbayMultiAccount\Helper\Data $helper
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->dataHelper = $helper;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $profileCode = $this->getRequest()->getParam('pcode');
        if ($profileCode) {
            $profile = $this->_objectManager->create('Ced\EbayMultiAccount\Model\Profile')->getCollection()->addFieldToFilter('profile_code', $profileCode)->getFirstItem();
        } else {
            $profile = $this->_objectManager->create('Ced\EbayMultiAccount\Model\Profile');
        }
        $this->getRequest()->setParam('is_profile', 1);
        $this->_coreRegistry->register('current_profile', $profile);
        $this->multiAccountHelper->getAccountRegistry($profile->getAccountId());
        $this->dataHelper->updateAccountVariable();

        $item = $profile->getId() ? $profile->getProfileName() : __('New Profile');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend($profile->getId() ? $profile->getProfileName() : __('New Profile'));
        $resultPage->getLayout()->getBlock('profile_edit_js')->setIsPopup((bool)$this->getRequest()->getParam('popup'));
        return $resultPage;
    }
}