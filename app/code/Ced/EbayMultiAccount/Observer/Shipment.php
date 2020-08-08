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

namespace Ced\EbayMultiAccount\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class Shipment implements ObserverInterface
{
    /**
     * Request
     * @var  \Magento\Framework\App\RequestInterface
     */
    public $request;

    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Registry
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * Shipment constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        ScopeConfigInterface $storeManager,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper
    )
    {
        $this->request = $request;
        $this->registry = $registry;
        $this->objectManager = $objectManager;
        $this->scopeConfigManager = $storeManager;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * Product SKU Change event handler
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function execute(Observer $observer)
    {
        try {
            $shipment = $observer->getEvent()->getShipment();
            $order = $shipment->getOrder();
            $shippingMethod = $order->getShippingMethod();
            $trackArray = [];
            foreach ($shipment->getAllTracks() as $track) {
                $trackArray = $track->getData();
            }

            if (empty($trackArray)) {
                return $observer;
            }
            $datahelper = $this->objectManager->get('Ced\EbayMultiAccount\Helper\Data');
            $incrementId = $order->getIncrementId();

            $ebaymultiaccountOrder = $this->objectManager->get('Ced\EbayMultiAccount\Model\Orders')->load($incrementId, 'magento_order_id');
            $ebaymultiaccountOrderId = $ebaymultiaccountOrder->getEbayMultiAccountOrderId();
            $accountId = $ebaymultiaccountOrder->getAccountId();
            if ($this->registry->registry('ebay_account'))
                $this->registry->unregister('ebay_account');
            $this->multiAccountHelper->getAccountRegistry($accountId);
            $datahelper->updateAccountVariable();
            if (empty($ebaymultiaccountOrderId)) {
                return $observer;
            }

            if ($ebaymultiaccountOrder->getEbayMultiAccountOrderId()) {

                $shipTodatetime = strtotime(date('Y-m-d H:i:s'));
                $deliverydate = date("Y-m-d", $shipTodatetime) . 'T' . date("H:i:s", $shipTodatetime);

                $orderData = json_decode($ebaymultiaccountOrder->getOrderData(), true);
                //after ack api end
                $trackNumber = "";
                if (isset($trackArray['track_number'])) {
                    $trackNumber = (string)$trackArray['track_number'];
                }

                $shipStationcarrier = isset($trackArray['carrier_code']) ? $trackArray['carrier_code'] :
                    $orderData->ShippingDetails->ShippingServiceOptions->ShippingService;

                $mappedShippingMethods = $this->scopeConfigManager->getValue('ebaymultiaccount_config/ebaymultiaccount_order/global_setting/carrier_mapping');
                if (!empty($mappedShippingMethods)) {
                    if (strpos($mappedShippingMethods, 's:') !== false) {
                        $mappedShippingMethods = unserialize($mappedShippingMethods);
                    } else {
                        $mappedShippingMethods = json_decode($mappedShippingMethods, true);
                    }
                }
                if (is_array($mappedShippingMethods)) {
                    $mappedShippingMethods = array_column($mappedShippingMethods, 'ebaymultiaccount_carrier', 'magento_carrier');
                }
                $shippingCarrierUsed = isset($mappedShippingMethods[$shipStationcarrier]) ? $mappedShippingMethods[$shipStationcarrier] : '';

                $itemsData = [];
                foreach ($order->getAllVisibleItems() as $item) {
                    $merchantSku = $item->getSku();
                    $quantityOrdered = $item->getQtyOrdered();
                    $quantityToShip = $item->getQtyShipped();
                    $itemsData [] = [
                        'sku' => $merchantSku,
                        'req_qty'=> $quantityOrdered,
                        'ship_qty' => $quantityToShip,
                        'cancel_quantity' => 0
                    ];
                }
                $shipData = [
                    'ship_todate' => $shipTodatetime,
                    'carrier' => $shippingCarrierUsed,
                    'tracking' => $trackNumber,
                    'items' => $itemsData
                ];
                if ($shipData) {
                    $data = $datahelper->createShipmentOrderBody(
                        $ebaymultiaccountOrderId,
                        $trackNumber,
                        $shippingCarrierUsed,
                        $deliverydate,
                        true
                    );
                    if ($data == 'Success') {
                        $ebaymultiaccountModel = $this->objectManager->get('Ced\EbayMultiAccount\Model\Orders')->load($incrementId, 'magento_order_id');
                        $ebaymultiaccountModel->setStatus('shipped');
                        $ebaymultiaccountModel->setShipmentData(json_encode($shipData));
                        $ebaymultiaccountModel->save();
                    }
                }
            }
        } catch (\Exception $e) {
            return $observer;
        }  
            
        return $observer;
    }
}
