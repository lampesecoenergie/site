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
 * @package   Ced_m2.2.EE
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Observer;

use Ced\Cdiscount\Helper\Order;
use Ced\Cdiscount\Model\Orders;

class Shipment implements \Magento\Framework\Event\ObserverInterface
{
    public $logger;
    public $orders;
    public $config;
    public $productChangeFactory;
    public $stockState;
    public $helperOrder;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\Framework\Registry $registry,
        \Ced\Cdiscount\Helper\Logger $logger,
        \Ced\Cdiscount\Helper\Order $helperOrder,
        \Ced\Cdiscount\Helper\Config $config,
        \Ced\Cdiscount\Model\Orders $orders,
        \Ced\Cdiscount\Model\ProductChangeFactory $changeFactory
    ) {
        $this->config = $config;
        $this->orders = $orders;
        $this->helperOrder = $helperOrder;
        $this->productChangeFactory = $changeFactory;
        $this->stockState = $stockState;
        $this->logger = $logger;
    }

    /*
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $response = true;
        $productId = 0;
        try {
            $data = [];
            $response = false;
            $shipment = $observer->getEvent()->getShipment();
            $order = $shipment->getOrder();
            $trackArray = [];
            foreach ($shipment->getAllTracks() as $allTrack) {
                $trackArray = $allTrack->getData();
            }
            $incrementId = $order->getIncrementId();
            $cdiscountOrder = $this->orders
                ->load($incrementId, 'increment_id');
            if ($cdiscountOrder->getData('shipments')) {
                return $observer;
            }
            $cdiscountOrderId = $cdiscountOrder->getCdiscountOrderId();
            if (empty($cdiscountOrderId)) {
                return $observer;
            } else {
                $data['OrderId'] = $cdiscountOrder->getData('magento_order_id');
                $data['CdiscountOrderID'] = $cdiscountOrderId;
                $data['ShippingProvider'] = $trackArray['title'];
                $data['TrackingNumber'] = $trackArray['track_number'];
            }
            if (isset($data) && !empty($data)) {
                $response = $this->helperOrder->shipOrder($data, true);
            }
            $this->logger->info('Shipment Response', ['path' => __METHOD__, 'data' => json_encode($response)]);
        } catch (\Exception $exception) {
            if ($this->config->getDebugMode() == true) {
                $this->logger->error(
                    $exception->getMessage(),
                    ['path' => __METHOD__, 'product_id' => $productId]
                );
            }
        }
        return $observer;
    }
}