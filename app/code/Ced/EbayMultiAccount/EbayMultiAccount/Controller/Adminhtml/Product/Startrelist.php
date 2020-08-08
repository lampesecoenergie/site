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
 * Class Startrelist
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\Product
 */
class Startrelist extends Action
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
     * Startrelist constructor.
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
                    $prodStatusAccAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($account->getId());
                    $this->dataHelper->updateAccountVariable();
                    $this->ebaymultiaccountHelper->updateAccountVariable();
                    foreach ($ids as $id) {
                        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id);
                        $ebaymultiaccountItemId = $product->getData($itemIdAccAttr);
                        if ($ebaymultiaccountItemId) {
                            $variable = "RelistItem";
                            $xmlHeader = $this->ebaymultiaccountHelper->prepareHeader($variable);
                            $xmlFooter = '</RelistItemRequest>';
                            $return = $xmlHeader . '<Item><ItemId>' . $ebaymultiaccountItemId . '</ItemId></Item>' . $xmlFooter;
                            $cpPath = $this->dataHelper->createFeed($return, $variable);
                            $relistOnEbayMultiAccount = $this->dataHelper->sendHttpRequest($return, $variable, 'server');
                            $this->dataHelper->responseParse($relistOnEbayMultiAccount, $variable, $cpPath);
                            if ($relistOnEbayMultiAccount->Ack == "Success" || $relistOnEbayMultiAccount->Ack == "Warning") {
                                $product->setData($prodStatusAccAttr, 4);
                                $product->setData($itemIdAccAttr, $relistOnEbayMultiAccount->ItemId);
                                $product->getResource()->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $itemIdAccAttr);
                                $success[] = $product->getSku();
                            } else if ($relistOnEbayMultiAccount->Ack == "Failure") {
                                if(isset($relistOnEbayMultiAccount->Errors)) {
                                    if (count($relistOnEbayMultiAccount->Errors) > 1) {
                                        foreach ($relistOnEbayMultiAccount->Errors as $value) {
                                            $errors[] = $value->LongMessage;
                                        }
                                    } else {
                                        $errors[] = $relistOnEbayMultiAccount->Errors->LongMessage;
                                    }
                                }
                                $message['error'] = $message['error'] . (isset($errors) ? implode(', ', $errors) : '') . " in product id(s)" . (isset($errorsku) ? implode(', ', $errorsku) : '');
                            }
                        } else {
                            $error[] = $product->getSku();
                        }
                    }
                }
                if (!empty($success)) {
                    $message['success'] = "Batch ". $index .": " .implode(', ', $success).": relist successfully";
                }
                if (!empty($error)) {
                    $message['error'] = "Batch ".$index. ": " .implode(', ', $error).": eBay Item Id Missing";
                }
            } else {
                $message['error'] = "Batch ".$index.": Product(s) data not found.";
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
