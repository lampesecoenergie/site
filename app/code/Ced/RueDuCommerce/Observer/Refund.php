<?php

namespace Ced\RueDuCommerce\Observer;

class Refund implements \Magento\Framework\Event\ObserverInterface
{
	protected $objectManager;
	protected $api;
	protected $logger;

	public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\RueDuCommerce\Helper\Logger $logger,
        \Ced\RueDuCommerce\Helper\Order $api,
        \Ced\RueDuCommerce\Model\OrdersFactory $orders,
        \Ced\RueDuCommerce\Helper\Config $config,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Framework\Message\ManagerInterface $manager,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->objectManager = $objectManager;
        $this->api = $api;
        $this->logger = $logger;
        $this->orders = $orders;
        $this->config = $config;
        $this->json = $json;
        $this->messageManager = $manager;
        $this->_request = $request;
    }
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
        $this->logger->log('INFO','Refund Observer Working');
        $refundOnRueDuCommerce = $this->config->getRefundOnRueDuCommerce();
        $refundSkus = [];
        try {
            if ($refundOnRueDuCommerce == "1") {
                $postData = $this->_request->getParams();
                if(isset($postData['order_id'])) {
                    $reason = (isset($postData['reason']) && $postData['reason'] != NULL) ? $postData['reason'] : $this->config->getRefundReason();
                    $creditMemo = $observer->getEvent()->getCreditmemo();
                    $creditMemoId = $creditMemo->getIncrementId();
                    $order = $creditMemo->getOrder();
                    $orderIncrementId = $order->getIncrementId();
                    $rueducommerceorder = $this->orders->create()->getCollection()->addFieldToFilter('increment_id', $orderIncrementId)->getFirstItem()->getData();
                    if (count($rueducommerceorder) <= 0) {
                        return $observer;
                    }
                    if (!$reason) {
                        $this->messageManager->addErrorMessage('RueDuCommerce Refund Reason is not selected.');
                        return $observer;
                    }
                    $item = array();
                    $cancelOrder = array(
                        'refund' => array(
                            '_attribute' => array(),
                            '_value' => array()
                        )
                    );
                    $rueducommerceorder_data = $this->json->jsonDecode($rueducommerceorder['order_data']);
                    $rueducommerceorder_data = $rueducommerceorder_data['order_lines']['order_line'];
                    $order_line_ids = array_column($rueducommerceorder_data, 'offer_sku');
                    foreach ($creditMemo->getAllItems() as $orderItems) {
                        $skuFound = array_search($orderItems->getSku(), $order_line_ids);
                        if ($skuFound !== FALSE) {
                            $refundSkus[] = $orderItems->getSku();
                            $item['amount'] = (string)$orderItems->getRowTotal();
                            $item['order_line_id'] = (string)$rueducommerceorder_data[$skuFound]['order_line_id'];
                            $item['quantity'] = (string)$orderItems->getQty();
                            $item['reason_code'] = (string)$reason;
                            $item['shipping_amount'] = (string)((float)$rueducommerceorder_data[$skuFound]['shipping_price'] / (float)$orderItems->getQty());
                        }
                        array_push($cancelOrder['refund']['_value'], $item);
                    }
                    $response = $this->api->refundOnRueDuCommerce($orderIncrementId, $cancelOrder, /*$creditMemoId*/
                        $order->getId());

                    $this->logger->info('Refund Observer Data', ['path' => __METHOD__, 'DataToRefund' => json_encode($cancelOrder), 'Response Data' => json_encode($response)]);

                    if (isset($response['body']['refunds'])) {
                        $refundSkus = implode(', ', $refundSkus);
                        $order->addStatusHistoryComment(__("Order Items ( $refundSkus ) Refunded with $reason reason On RueDuCommerce."))
                            ->setIsCustomerNotified(false)->save();
                        $this->logger->info('Refund Success', ['path' => __METHOD__, 'RefundSkus' => $refundSkus, 'Reason' => $reason, 'Increment Id' => $orderIncrementId]);
                        $this->messageManager->addSuccessMessage('Refund Successfully Generated on RueDuCommerce');
                    } else {
                        $this->logger->info('Refund Fail', ['path' => __METHOD__, 'DataToRefund' => json_encode($cancelOrder), 'Response Data' => json_encode($response)]);
                        $this->messageManager->addErrorMessage('Error Generating Refund on RueDuCommerce. Please process from merchant panel.');
                    }
                }
                return $observer;
            }
        } catch (\Exception $e) {
            $this->logger->error('Refund Observer', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $observer;
        }
        return $observer;
	}
}