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
 * Class Enditem
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\Product
 */
class Enditem extends Action
{
    /**
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
     * Enditem constructor.
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
        $sku = '';
        try {
            $id = $this->getRequest()->getParam('id');
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id);
            $sku = $product->getSku();
            $ebaymultiaccountItemId = $product->getEbayMultiAccountItemId();
            if ($ebaymultiaccountItemId) {
                $variable = "EndItem";
                $finalXml = $this->ebaymultiaccountHelper->endListing($product);
                if ($finalXml['type'] == 'success') {
                    $finalXml = $finalXml['data'];
                }
                $xmlHeader = $this->ebaymultiaccountHelper->prepareHeader($variable);
                $xmlFooter = '</EndItemRequest>';
                $return = $xmlHeader . $finalXml . $xmlFooter;
                $endListing = $this->dataHelper->sendHttpRequest($return, $variable, 'server');
                if ($endListing->Ack == 'Success') {
                    $product->setEbayMultiAccountProductStatus(5);
                    $product->getResource()->save($product);
                    $msg = $sku . " successfully ended on eBay";
                }
            }
        } catch (\Exception $e) {
            $msg = 'Exception in SKU: (' . $sku . ") " . $e->getMessage();
            $this->logger->addError('In Enditem Call: ' . $e->getMessage(), ['path' => __METHOD__]);
        }
        $this->messageManager->addNoticeMessage($msg);
        $this->_redirect('ebaymultiaccount/product/index');
    }
}