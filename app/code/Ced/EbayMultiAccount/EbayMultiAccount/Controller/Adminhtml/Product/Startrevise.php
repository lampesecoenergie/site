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
 * Class Startrevise
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\Product
 */
class Startrevise extends Action
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
     * Startrevise constructor.
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
        $message = $error = $success =[];
        $message['error'] = "";
        $message['success'] = "";

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
                    $itemIdAccAttr = $this->multiAccountHelper->getItemIdAttrForAcc($account->getId());
                    $this->dataHelper->updateAccountVariable();
                    $this->ebaymultiaccountHelper->updateAccountVariable();
                    foreach ($prodIds as $id) {
                        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id);
                        if ($product->getData($itemIdAccAttr)) {
                            $finaldata = $this->ebaymultiaccountHelper->prepareData($product);
                            if ($finaldata['type'] == 'success') {
                                if ($product->getTypeId() == 'configurable') {
                                    $variable = "ReviseFixedPriceItem";
                                    $xmlHeader = $this->ebaymultiaccountHelper->prepareHeader($variable);
                                    $xmlFooter = '</ReviseFixedPriceItemRequest>';
                                } else {
                                    $variable = "ReviseItem";
                                    $xmlHeader = $this->ebaymultiaccountHelper->prepareHeader($variable);
                                    $xmlFooter = '</ReviseItemRequest>';
                                }
                                $finalXml = str_replace('<?xml version="1.0"?>', '', $finaldata['data']);
                                $return = $xmlHeader . $finalXml . $xmlFooter;
                                $cpPath = $this->dataHelper->createFeed($return, $variable);
                                $reviseItem = $this->dataHelper->sendHttpRequest($return, $variable, 'server');
                                $this->dataHelper->responseParse($reviseItem, $variable, $cpPath);
                                if ($reviseItem->Ack == "Success" || $reviseItem->Ack == "Warning") {
                                    $successids[] = $product->getSku();
                                    $message['success'] = implode(', ', $successids)." successfully updated.";
                                } elseif ($reviseItem->Ack == "Failure") {
                                    $errorResponse = $this->getErrors($reviseItem->Errors);
                                    $message['error'] .= $product->getSku() . ": " . $errorResponse;
                                }
                            } else {
                                $message['error'] .= $finaldata['data'];
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
