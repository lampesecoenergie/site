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

namespace Ced\EbayMultiAccount\Controller\Adminhtml\Order;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;


class Ship extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_EbayMultiAccount::EbayMultiAccount';
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * Ship constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ced\EbayMultiAccount\Helper\Logger $logger,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        $this->_coreRegistry = $registry;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * @return string
     */

    public function execute()
    {
        try {
            $datahelper = $this->_objectManager->get('Ced\EbayMultiAccount\Helper\Data');
            $orderhelper = $this->_objectManager->get('Ced\EbayMultiAccount\Helper\Order');
            $ebaymultiaccounthelper = $this->_objectManager->get('Ced\EbayMultiAccount\Helper\EbayMultiAccount');

            // collect ship data
            $postData = $this->getRequest()->getPost();
            $shipTodatetime = strtotime($postData['ship_todate']);
            $deliverydate = date("Y-m-d", $shipTodatetime) . 'T' . date("H:i:s", $shipTodatetime);
            $id = $postData['id'];
            $orderId = $postData['magento_orderid'];
            $incrementOrderId = $postData['incrementid'];
            $mageOrderId = $postData['magento_orderid'];
            $shippingCarrierUsed = $postData['carrier'];
            $ebaymultiaccountOrderId = $postData['ebaymultiaccountorderid'];
            $trackNumber = $postData['tracking'];
            $itemsData = json_decode($postData['items'], true);
            if (empty($itemsData)) {
                $this->getResponse()->setBody("You have no item in your Order.");
                return;
            }
            $shipData = [
                'ship_todate' => $shipTodatetime,
                'carrier' => $shippingCarrierUsed,
                'tracking' => $trackNumber,
                'items' => $itemsData
            ];
            $shipQtyForOrder = $cancelQtyForOrder = [];
            foreach ($itemsData as $value) {
                if ($value['ship_qty'] == $value['req_qty']) {
                    $shipment = true;
                }
                if ($value['cancel_quantity'] == $value['req_qty']) {
                    $shipment = false;
                }
                if ($value['ship_qty'] > 0) {
                    $shipQtyForOrder[$value['sku']] = $value['ship_qty'];
                }
                if ($value['cancel_quantity'] > 0) {
                    $cancelQtyForOrder[$value['sku']] = $value['cancel_quantity'];
                }
            }

            $ebaymultiaccountModel = $this->_objectManager->create('Ced\EbayMultiAccount\Model\Orders')->load($id);
            $accountId = $ebaymultiaccountModel->getAccountId();
            if ($this->_coreRegistry->registry('ebay_account'))
                $this->_coreRegistry->unregister('ebay_account');
            $this->multiAccountHelper->getAccountRegistry($accountId);
            $datahelper->updateAccountVariable();
            $data = $datahelper->createShipmentOrderBody($ebaymultiaccountOrderId, $trackNumber, $shippingCarrierUsed, $deliverydate, $shipment);
            if ($data == 'Success') {
                $order = $this->_objectManager->get(
                    'Magento\Sales\Model\Order')->loadByIncrementId($incrementOrderId);
                $itemQty = [];
                $itemQtytoCancel = [];
                foreach ($order->getAllVisibleItems() as $item) {
                    $shipSku = $item->getSku();
                    if (isset($shipQtyForOrder[$shipSku])) {
                        $itemQty[$item->getId()] = $shipQtyForOrder[$shipSku];
                    }
                    if (isset($cancelQtyForOrder[$shipSku])) {
                        $itemQtytoCancel[$item->getId()] = $cancelQtyForOrder[$shipSku];
                    }
                }
                if (!empty($itemQty)) {
                    if ($order->canShip()) {
                        $orderhelper->generateShipment($order, $itemQty);
                    }
                }
                if (!empty($itemQtytoCancel)) {
                    $orderhelper->generateCreditMemo($order, $itemQtytoCancel);
                }
                $ebaymultiaccountModel->setStatus('shipped');
                $ebaymultiaccountModel->setShipmentData(json_encode($shipData));
                $ebaymultiaccountModel->save();
                $this->messageManager->addSuccessMessage('Your EbayMultiAccount Order ' . $incrementOrderId . ' has been Completed');
                $this->getResponse()->setBody("Success");
            } else {
                $this->getResponse()->setBody($data);
                return;
            }
        } catch (\Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
            $this->logger->addError('In Fetch Order: '.$e->getMessage(), ['path' => __METHOD__]);
        }
    }
}
