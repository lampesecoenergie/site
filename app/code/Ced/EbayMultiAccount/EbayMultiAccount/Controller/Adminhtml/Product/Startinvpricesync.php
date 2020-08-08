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

namespace Ced\EbayMultiAccount\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Ced\EbayMultiAccount\Helper\EbayMultiAccount;
use Ced\EbayMultiAccount\Helper\Data;
use Ced\EbayMultiAccount\Helper\Logger;

/**
 * Class Startinvpricesync
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\Product
 */
class Startinvpricesync extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;
    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;
    /**
     * @var EbayMultiAccount
     */
    public $ebaymultiaccountHelper;
    /**
     * @var Data
     */
    public $dataHelper;
    /**
     * @var Logger
     */
    public $logger;

    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Startinvpricesync constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param EbayMultiAccount $ebaymultiaccountHelper
     * @param Data $dataHelper
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        EbayMultiAccount $ebaymultiaccountHelper,
        Data $dataHelper,
        Logger $logger,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->ebaymultiaccountHelper = $ebaymultiaccountHelper;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->_coreRegistry = $coreRegistry;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $message = [];
        $message['error'] = "";
        $message['success'] = "";
        $finalXml = '';
        $error = $successids ='';

        $key = $this->getRequest()->getParam('index');
        $totalChunk = $this->_session->getUploadChunks();
        $index = $key + 1;
        if (count($totalChunk) <= $index) {
            $this->_session->unsUploadChunks();
        }
        try {
            if (isset($totalChunk[$key])) {
                $ids = $totalChunk[$key];
                foreach ($ids as $accountId => $prodIds) {
                    if (!is_array($prodIds)) {
                        $prodIds[] = $prodIds;
                    }
                    if ($this->_coreRegistry->registry('ebay_account'))
                        $this->_coreRegistry->unregister('ebay_account');
                    $this->multiAccountHelper->getAccountRegistry($accountId);
                    $this->dataHelper->updateAccountVariable();
                    $this->ebaymultiaccountHelper->updateAccountVariable();
                    $checkError = false;
                    foreach ($prodIds as $id) {
                        $finaldata = $this->ebaymultiaccountHelper->getInventoryPrice($id);
                        if ($finaldata['type'] == 'success') {
                            $checkError = true;
                            $finalXml .= $finaldata['data'];
                        } else {
                            $error .= $finaldata['data'];
                        }
                    }
                    if ($error) {
                        $message['error'] = $error;
                    }
                    if ($checkError) {
                        $variable = "ReviseInventoryStatus";
                        $xmlHeader = $this->ebaymultiaccountHelper->prepareHeader($variable);
                        $xmlFooter = '</ReviseInventoryStatusRequest>';
                        $return = $xmlHeader . $finalXml . $xmlFooter;
                        $cpPath = $this->dataHelper->createFeed($return, $variable);
                        $invPriceSyncOnEbayMultiAccount = $this->dataHelper->sendHttpRequest($return, $variable, 'server');
                        $this->dataHelper->responseParse($invPriceSyncOnEbayMultiAccount, $variable, $cpPath);
                        if ($invPriceSyncOnEbayMultiAccount->Ack == "Success" || $invPriceSyncOnEbayMultiAccount->Ack == "Warning") {
                            if (isset($invPriceSyncOnEbayMultiAccount->Errors)) {
                                $message['error'] = $this->getErrors($invPriceSyncOnEbayMultiAccount->Errors);
                            }
                            $message['success'] = 'success';
                        } else if ($invPriceSyncOnEbayMultiAccount->Ack == "Failure") {
                            if (isset($invPriceSyncOnEbayMultiAccount->Errors)) {
                                $message['error'] = $this->getErrors($invPriceSyncOnEbayMultiAccount->Errors);
                            }
                        } else if ($invPriceSyncOnEbayMultiAccount->Ack == 'PartialFailure') {
                            if (isset($invPriceSyncOnEbayMultiAccount->Errors)) {
                                $message['error'] = $this->getErrors($invPriceSyncOnEbayMultiAccount->Errors);
                            }
                        }
                    }
                }
                if (!empty($message['success'])) {
                    $message['success'] = "Batch ". $index .": " .$message['success'];
                }
                if (!empty($message['error'])) {
                    $message['error'] = "Batch ".$index. ": " .$message['error'];
                }
            } else {
                $message['error'] = "Batch ".$index.": ".$message['error']." included Product(s) data not found.";
            }
        } catch (\Exception $e) {
            $message['error'] = $e->getMessage();
            $this->logger->addError($message['error'], ['path' => __METHOD__]);
        }
        return $resultJson->setData($message);
    }

    /**
     * @param $invPriceSyncOnEbayMultiAccount
     * @return string
     */
    public function getErrors($invPriceSyncOnEbayMultiAccount)
    {
        $message = [];
        if (!isset($invPriceSyncOnEbayMultiAccount->LongMessage)) {
            foreach ($invPriceSyncOnEbayMultiAccount as $errorMessage) {
                $message[] =  $errorMessage->LongMessage;
            }
        } else {
            $message[] = $invPriceSyncOnEbayMultiAccount->LongMessage;
        }
        return implode(', ', $message);
    }
}
