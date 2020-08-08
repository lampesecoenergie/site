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

/**
 * Class Additem
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\Product
 */
class Additem extends Action
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
     * @var \Ced\EbayMultiAccount\Helper\EbayMultiAccount
     */
    public $ebaymultiaccountHelper;
    /**
     * @var \Ced\EbayMultiAccount\Helper\Logger
     */
    public $logger;

    /**
     * Additem constructor.
     * @param Action\Context $context
     * @param \Ced\EbayMultiAccount\Helper\Data $dataHelper
     * @param \Ced\EbayMultiAccount\Helper\EbayMultiAccount $ebaymultiaccountHelper
     * @param \Ced\EbayMultiAccount\Helper\Logger $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ced\EbayMultiAccount\Helper\Data $dataHelper,
        \Ced\EbayMultiAccount\Helper\EbayMultiAccount $ebaymultiaccountHelper,
        \Ced\EbayMultiAccount\Helper\Logger $logger
    )
    {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
        $this->ebaymultiaccountHelper = $ebaymultiaccountHelper;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        try {
            $errorResponse = [];
            $variable = "Uplaod Product";
            $id = $this->getRequest()->getParam('id');
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id);
            $ebaymultiaccountItemId = $product->getEbayMultiAccountItemId();
            $update = false;
            $finaldata = $this->ebaymultiaccountHelper->prepareData($product);
            if ($finaldata['type'] == 'success') {
                $finalXml = str_replace('<?xml version="1.0"?>', '', $finaldata['data']);
                if (empty($ebaymultiaccountItemId)) {
                    $update = true;
                    if ($product->getTypeId() == 'simple') {
                        $variable = "AddItem";
                        $xmlHeader = $this->ebaymultiaccountHelper->prepareHeader($variable);
                        $xmlFooter = '</AddItemRequest>';
                    } else {
                        $variable = "AddFixedPriceItem";
                        $xmlHeader = $this->ebaymultiaccountHelper->prepareHeader($variable);
                        $xmlFooter = '</AddFixedPriceItemRequest>';
                    }
                } else if ($ebaymultiaccountItemId){
                    if ($product->getTypeId() == 'simple') {
                        $variable = "ReviseItem";
                        $xmlHeader = $this->ebaymultiaccountHelper->prepareHeader($variable);
                        $xmlFooter = '</ReviseItemRequest>';
                    } else {
                        $variable = "ReviseFixedPriceItem";
                        $xmlHeader = $this->ebaymultiaccountHelper->prepareHeader($variable);
                        $xmlFooter = '</ReviseFixedPriceItemRequest>';
                    }
                }
                $return = $xmlHeader . $finalXml . $xmlFooter;
                $uploadOnEbayMultiAccount = $this->dataHelper->sendHttpRequest($return, $variable, 'server');

                if ($uploadOnEbayMultiAccount->Ack == "Success" || $uploadOnEbayMultiAccount->Ack == "Warning") {
                    if ($update) {
                        $ebaymultiaccountItemId = $uploadOnEbayMultiAccount->ItemID;
                        $product->setEbayMultiAccountProductStatus(4);
                        $product->setEbayMultiAccountListingError(json_encode(["valid"]));
                        $product->setEbayMultiAccountItemId($ebaymultiaccountItemId);
                        $msg = $product->getSku().": "." Successfully Uploaded";
                    } else {
                        $msg = $product->getSku().": "." Successfully Synced";
                    }
                } elseif ($uploadOnEbayMultiAccount->Ack == "Failure") {
                    if (!isset($uploadOnEbayMultiAccount->Errors->LongMessage)) {
                        foreach ($uploadOnEbayMultiAccount->Errors as $value) {
                            $errorResponse[] = $value->LongMessage;
                        }
                    } else {
                        $errorResponse[] = $uploadOnEbayMultiAccount->Errors->LongMessage;
                    }
                    if ($update) {
                        $listingError = $this->preapareResponse($variable, $product->getSku(), $errorResponse);
                        $product->setEbayMultiAccountProductStatus(2);
                        $product->setEbayMultiAccountListingError($listingError);
                    }
                    $msg = $product->getSku().": ".implode(', ', $errorResponse);
                }
                $product->getResource()->save($product);
            } else {
                if ($update) {
                    $listingError = $this->preapareResponse($variable, $product->getSku(), [$finaldata['data']]);
                    $product->setEbayMultiAccountListingError($listingError);
                    $product->setEbayMultiAccountProductStatus(2);
                    $product->getResource()->save($product);
                }
                $msg = $product->getSku().": ".$finaldata['data'];
            }            
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $this->logger->addError('In AddItem Call: '.$e->getMessage(), ['path' => __METHOD__]);
        }       
        $this->messageManager->addNoticeMessage($msg);
        $this->_redirect('ebaymultiaccount/product/index');
    }

    /**
     * @param $variable
     * @param sku
     * @param errors
     * @return string
     */
    public function preapareResponse($variable, $sku, $errors)
    {
        $response = [];
        $response[$variable] = 
            [
                "sku" => $sku,
                "url" => "#",
                'errors' => $errors
            ];

        return json_encode($response);
    }
}