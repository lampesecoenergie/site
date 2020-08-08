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
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Account;

/**
 * Class Edit
 *
 * @package Ced\Amazon\Controller\Adminhtml\Account
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * @var \Ced\Amazon\Model\ResourceModel\AccountFactory
     */
    public $account;

    /**
     * @var \Ced\Amazon\Model\ResourceModel\AccountFactory
     */
    public $accountResource;

    /**
     * @var \Ced\Amazon\Helper\Config
     */
    public $config;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ced\Amazon\Model\ResourceModel\AccountFactory $accountResourceFactory,
        \Ced\Amazon\Model\AccountFactory $accountFactory,
        \Ced\Amazon\Helper\Config $config
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->account = $accountFactory;
        $this->accountResource = $accountResourceFactory;
        $this->config = $config;
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var \Ced\Amazon\Model\Account $account */
        $account = $this->account->create();

        $title = 'Add Account';
        if (isset($id) && !empty($id)) {
            /** @var \Ced\Amazon\Model\ResourceModel\Account $accountResource */
            $accountResource = $this->accountResource->create();
            $accountResource->load($account, $id);
            $title = 'Edit Account';
        }

        $this->coreRegistry->register('amazon_account', $account);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Amazon::account');
        $resultPage->getConfig()->getTitle()->prepend(__($title));
        return $resultPage;
    }
}
