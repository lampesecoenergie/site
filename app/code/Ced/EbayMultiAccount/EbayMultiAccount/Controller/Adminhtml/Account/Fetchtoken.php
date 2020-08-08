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

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Fetchtoken
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\Account
 */
class Fetchtoken extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_EbayMultiAccount::EbayMultiAccount';
    /**
     * @var \Ced\EbayMultiAccount\Helper\Data
     */
    public $dataHelper;
    /**
     * @var \Ced\EbayMultiAccount\Helper\Logger
     */
    public $logger;
    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    public $multiAccountHelper;

    /**
     * @var \Ced\EbayMultiAccount\Model\AccountsFactory
     */
    public $accounts;

    /**
     * Fetchtoken constructor.
     * @param Action\Context $context
     * @param \Ced\EbayMultiAccount\Helper\Data $dataHelper
     * @param \Ced\EbayMultiAccount\Helper\Logger $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ced\EbayMultiAccount\Helper\Data $dataHelper,
        \Ced\EbayMultiAccount\Helper\Logger $logger,
        \Ced\EbayMultiAccount\Model\AccountsFactory $accounts,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper
    )
    {
        parent::__construct($context);
        $this->multiAccountHelper = $multiAccountHelper;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->accounts = $accounts;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        try {
            $data = $this->getRequest()->getParams();
            if (isset($data['id'])) {
                $this->multiAccountHelper->getAccountRegistry($data['id']);
                $this->dataHelper->updateAccountVariable();
                $accounts = $this->accounts->create()->load($data['id']);
            }
            if (isset($data['status'])) {
                if ($data['status'] == "success") {
                    $response = $this->dataHelper->fetchToken();
                    if ($response != '') {
                        $msg = 'Token fetch successfully';
                        $accounts->setAccountToken($response);
                        $accounts->save();
                    } else {
                        $msg = 'Token Generation Failed. Please Try Again';
                    }
                }
            } else {
                if (!empty($data)) {
                    $response = $this->dataHelper->getSessionId();
                    if ($response['msg'] == "success") {
                        $msg = 'session generated successfully';
                        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                        $resultRedirect->setUrl($response['data']);
                        return $resultRedirect;
                    } else {
                        $msg = 'Session Id generation failed';
                    }
                } else {
                    $msg = 'post data is empty';
                }
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $this->logger->addError('In Fetch Token Call: '.$e->getMessage(), ['path' => __METHOD__]);
        }       
        $this->messageManager->addNoticeMessage($msg);
        $this->_redirect('ebaymultiaccount/account/index');
    }
}