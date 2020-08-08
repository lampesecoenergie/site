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
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Account\Marketplace;

/**
 * Class View
 * @package Ced\Amazon\Controller\Adminhtml\Account\Marketplace
 */
class View extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Amazon::account';

    /** @var \Ced\Amazon\Model\AccountFactory  */
    public $account;

    /**
     * Json Factory
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    /**
     * View constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Ced\Amazon\Model\AccountFactory $account
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ced\Amazon\Model\AccountFactory $account
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->account = $account;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $marketplaceIds = [];
        $id = $this->getRequest()->getParam('id');
        /** @var \Ced\Amazon\Model\Account $account */
        $account = $this->account->create()->load($id);
        if (!empty($account) && $account->getId() > 0) {
            $marketplaceIds = $account->getMarketplaceIds();
        }

        return $this->resultJsonFactory
            ->create()
            ->setData($marketplaceIds);
    }
}
