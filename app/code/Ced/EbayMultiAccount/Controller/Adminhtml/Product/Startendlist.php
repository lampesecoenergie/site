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
 * Class Startendlist
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\Product
 */
class Startendlist extends Action
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
     * Startendlist constructor.
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
        $error = $successids = $error = [];

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
                    $account = $this->multiAccountHelper->getAccountRegistry($accountId);
                    $prodStatusAccAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($account->getId());
                    $this->dataHelper->updateAccountVariable();
                    $this->ebaymultiaccountHelper->updateAccountVariable();
                    $checkError = false;
                    foreach ($prodIds as $id) {
                        $finaldata = $this->ebaymultiaccountHelper->endListing($id, 1);
                        if ($finaldata['type'] == 'success') {
                            $checkError = true;
                            $finalXml .= $finaldata['data'];
                        } else {
                            $error[] = $finaldata['data'];
                        }
                    }
                    if (!empty($error)) {
                        $message['error'] = implode(', ', $error);
                    }
                    if ($checkError) {
                        $variable = "EndItems";
                        $xmlHeader = $this->ebaymultiaccountHelper->prepareHeader($variable);
                        $xmlFooter = '</EndItemsRequest>';
                        $return = $xmlHeader . $finalXml . $xmlFooter;
                        $cpPath = $this->dataHelper->createFeed($return, $variable);
                        $endListing = $this->dataHelper->sendHttpRequest($return, $variable, 'server');
                        $this->dataHelper->responseParse($endListing, $variable, $cpPath);
                        if ($endListing->Ack == "Success" || $endListing->Ack == "Warning") {
                            if (isset($endListing->EndItemResponseContainer->CorrelationID)) {
                                $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($endListing->EndItemResponseContainer->CorrelationID);
                                $product->setData($prodStatusAccAttr, 5);
                                $product->getResource()->save($product);
                                $successids[] = $product->getSku();
                            } else {
                                foreach ($endListing->EndItemResponseContainer as $value) {
                                    $corID = $value->CorrelationID;
                                    $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($value->CorrelationID);
                                    $product->setData($prodStatusAccAttr, 5);
                                    $product->getResource()->saveAttribute($product, $prodStatusAccAttr);
                                    $successids[] = $product->getSku();
                                }
                            }
                            $message['success'] = "SKU(s): " . implode(', ', $successids) . "successfully ended";
                        } else if ($endListing->Ack == "Failure") {
                            if(isset($endListing->EndItemResponseContainer)) {
                                if (isset($endListing->EndItemResponseContainer->CorrelationID)) {
                                    if (count($endListing->EndItemResponseContainer->Errors) > 1) {
                                        foreach ($endListing->EndItemResponseContainer->Errors as $value) {
                                            $errors[] = $value->LongMessage;
                                        }
                                    } else {
                                        $errors[] = $endListing->EndItemResponseContainer->Errors->LongMessage;
                                    }
                                    $errorsku[] = $endListing->EndItemResponseContainer->CorrelationID;
                                } else {
                                    foreach ($endListing->EndItemResponseContainer as $value) {
                                        $errorsku[] = $value->CorrelationID;
                                        if (count($value->Errors) > 1) {
                                            foreach ($value->Errors as $v) {
                                                $errors[] = $v->LongMessage;
                                            }
                                        } else {
                                            $errors[] = $value->Errors->LongMessage;
                                        }
                                    }
                                }
                            } elseif(isset($endListing->Errors)) {
                                if (count($endListing->Errors) > 1) {
                                    foreach ($endListing->Errors as $value) {
                                        $errors[] = $value->LongMessage;
                                    }
                                } else {
                                    $errors[] = $endListing->Errors->LongMessage;
                                }
                            }
                            $message['error'] = $message['error'] . (isset($errors) ? implode(', ', $errors) : '') . " in product id(s)" . (isset($errorsku) ? implode(', ', $errorsku) : '');
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
}
