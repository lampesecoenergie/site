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
 * Class Startupload
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\Product
 */
class Startupload extends Action
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
     * Startupload constructor.
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
        $successids = [];
        $variable = "AddFixedPriceItem";
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
                    $itemIdAccAttr = $this->multiAccountHelper->getItemIdAttrForAcc($accountId);
                    $listingErrorAccAttr = $this->multiAccountHelper->getProdListingErrorAttrForAcc($accountId);
                    $prodStatusAccAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($accountId);
                    $this->dataHelper->updateAccountVariable();
                    $this->ebaymultiaccountHelper->updateAccountVariable();
                    foreach ($prodIds as $id) {
                        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id);
                        if ($product->getTypeId() == 'configurable') {
                            $finaldata = $this->ebaymultiaccountHelper->prepareData($id);
                            if ($finaldata['type'] == 'success') {
                                $xmlHeader = $this->ebaymultiaccountHelper->prepareHeader($variable);
                                $xmlFooter = '</AddFixedPriceItemRequest>';
                                $cfinalXml = str_replace('<?xml version="1.0"?>', '', $finaldata['data']);
                                $return = $xmlHeader . $cfinalXml . $xmlFooter;
                                $cpPath = $this->dataHelper->createFeed($return, $variable);
                                $addFixedPriceItem = $this->dataHelper->sendHttpRequest($return, $variable, 'server');
                                $this->dataHelper->responseParse($addFixedPriceItem, $variable, $cpPath);
                                if ($addFixedPriceItem->Ack == "Success" || $addFixedPriceItem->Ack == "Warning") {
                                    $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($addFixedPriceItem->CorrelationID);
                                    $ebaymultiaccountItemId = $addFixedPriceItem->ItemID;
                                    $product->setData($prodStatusAccAttr, 4);
                                    $product->setData($listingErrorAccAttr, json_encode(["valid"]));
                                    $product->setData($itemIdAccAttr, $ebaymultiaccountItemId);
                                    $successids[] = $product->getSku();
                                } elseif ($addFixedPriceItem->Ack == "Failure") {
                                    $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($addFixedPriceItem->CorrelationID);
                                    $errorResponse = $this->getErrors($addFixedPriceItem->Errors);
                                    $listingError = $this->preapareResponse($product->getEntityId(), $variable, $product->getSku(), $errorResponse);
                                    $product->setData($prodStatusAccAttr, 2);
                                    $product->setData($listingErrorAccAttr, $listingError);
                                    $message['error'] .= $product->getSku() . ": " . $errorResponse;
                                }
                                $product->getResource()->save($product);
                            } else {
                                $listingError = $this->preapareResponse($product->getEntityId(), $variable, $product->getSku(), [$finaldata['data']]);
                                $product->setData($prodStatusAccAttr, 2);
                                $product->setData($listingErrorAccAttr, $listingError);
                                $product->getResource()->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $listingErrorAccAttr);
                                $message['error'] .= $finaldata['data'];
                            }
                        } else {
                            $finaldata = $this->ebaymultiaccountHelper->prepareData($id, 1);
                            if ($finaldata['type'] == 'success') {
                                $finalXml .= str_replace('<?xml version="1.0"?>', '', $finaldata['data']);
                            } else {
                                $listingError = $this->preapareResponse($product->getEntityId(), $variable, $product->getSku(), [$finaldata['data']]);
                                $product->setData($prodStatusAccAttr, 2);
                                $product->setData($listingErrorAccAttr, $listingError);
                                $product->getResource()->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $listingErrorAccAttr);
                                $message['error'] .= $finaldata['data'];
                            }
                        }
                    }

                    if ($finalXml) {
                        $variable = "AddItems";
                        $xmlHeader = $this->ebaymultiaccountHelper->prepareHeader($variable);
                        $xmlFooter = '</AddItemsRequest>';
                        $return = $xmlHeader . $finalXml . $xmlFooter;
                        $cpPath = $this->dataHelper->createFeed($return, $variable);
                        $addItems = $this->dataHelper->sendHttpRequest($return, $variable, 'server');
                        $this->dataHelper->responseParse($addItems, $variable, $cpPath);
                        if ($addItems->Ack == "Warning" || $addItems->Ack == "Success") {
                            if (isset($addItems->AddItemResponseContainer->ItemID)) {
                                $corID = $addItems->AddItemResponseContainer->CorrelationID;
                                $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->loadByAttribute('entity_id', $corID);
                                $product->setData($prodStatusAccAttr, 4);
                                $product->setData($listingErrorAccAttr, json_encode(["valid"]));
                                $product->setData($itemIdAccAttr, $addItems->AddItemResponseContainer->ItemID);
                                $product->getResource()->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $listingErrorAccAttr)->saveAttribute($product, $itemIdAccAttr);
                                $successids[] = $product->getSku();
                            } else {
                                foreach ($addItems->AddItemResponseContainer as $value) {
                                    $corID = $value->CorrelationID;
                                    $ebaymultiaccountID = $value->ItemID;
                                    $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->loadByAttribute('entity_id', $corID);
                                    $product->setData($prodStatusAccAttr, 4);
                                    $product->setData($listingErrorAccAttr, json_encode(["valid"]));
                                    $product->setData($itemIdAccAttr, $ebaymultiaccountID);
                                    $product->getResource()->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $listingErrorAccAttr)->saveAttribute($product, $itemIdAccAttr);
                                    $successids[] = $product->getSku();
                                }
                            }
                        } else if ($addItems->Ack == "PartialFailure") {
                            foreach ($addItems->AddItemResponseContainer as $value) {
                                $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->loadByAttribute('entity_id', $value->CorrelationID);
                                if (isset($value->ItemID)) {
                                    $ebaymultiaccountID = $value->ItemID;
                                    $product->setData($prodStatusAccAttr, 4);
                                    $product->setData($listingErrorAccAttr, json_encode(["valid"]));
                                    $product->setData($itemIdAccAttr, $ebaymultiaccountID);
                                    $product->getResource()->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $listingErrorAccAttr)->saveAttribute($product, $itemIdAccAttr);
                                    $successids[] = $product->getSku();
                                } elseif (isset($value->Errors)) {
                                    $errorResponse = $this->getErrors($value->Errors);
                                    $listingError = $this->preapareResponse($product->getEntityId(), $variable, $product->getSku(), $errorResponse);
                                    $product->setData($prodStatusAccAttr, 2);
                                    $product->setData($listingErrorAccAttr, $listingError);
                                    $product->getResource()->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $listingErrorAccAttr);
                                    $message['error'] .= $product->getSku() . ": " . $errorResponse;
                                }

                            }
                        } else if ($addItems->Ack == "Failure") {
                            if (isset($addItems->AddItemResponseContainer)) {
                                if (isset($addItems->AddItemResponseContainer->CorrelationID)) {
                                    $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->loadByAttribute('entity_id', $addItems->AddItemResponseContainer->CorrelationID);
                                    $corID = $addItems->AddItemResponseContainer->CorrelationID;
                                    $errorResponse = $this->getErrors($addItems->AddItemResponseContainer->Errors);
                                    $listingError = $this->preapareResponse($product->getEntityId(), $variable, $product->getSku(), $errorResponse);
                                    $product->setData($prodStatusAccAttr, 2);
                                    $product->setData($listingErrorAccAttr, $listingError);
                                    $product->getResource()->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $listingErrorAccAttr);
                                    $message['error'] .= $product->getSku() . ": " . $errorResponse;
                                } else {
                                    foreach ($addItems->AddItemResponseContainer as $value) {
                                        $corID = $value->CorrelationID;
                                        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->loadByAttribute('entity_id', $corID);
                                        $errorResponse = $this->getErrors($value->Errors);
                                        $listingError = $this->preapareResponse($product->getEntityId(), $variable, $product->getSku(), $errorResponse);
                                        $product->setData($prodStatusAccAttr, 2);
                                        $product->setData($listingErrorAccAttr, $listingError);
                                        $product->getResource()->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $listingErrorAccAttr);
                                        $message['error'] .= $product->getSku() . ": " . $errorResponse;
                                    }
                                }
                            } else {
                                $errorResponse = $this->getErrors($addItems->Errors);
                                $message['error'] .= $errorResponse;
                            }
                        }
                    }
                }
                if (!empty($successids)) {
                    $message['success'] = "Batch ". $index .": " .implode(', ', $successids)." successfully uploaded";
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

    /**
     * @param $variable
     * @param sku
     * @param id
     * @param errors
     * @return string
     */
    public function preapareResponse($id=null, $variable, $sku, $errors)
    {
        $response = [];
        $response[$variable] = 
            [
                "id" => $id,
                "sku" => $sku,
                "url" => "#",
                'errors' => $errors
            ];

        return json_encode($response);
    }
}
