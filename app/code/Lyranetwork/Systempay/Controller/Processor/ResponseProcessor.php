<?php
/**
 * Systempay V2-Payment Module version 2.3.2 for Magento 2.x. Support contact : supportvad@lyra-network.com.
 *
 * NOTICE OF LICENSE
 *
 * This source file is licensed under the Open Software License version 3.0
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 *
 * @category  Payment
 * @package   Systempay
 * @author    Lyra Network (http://www.lyra-network.com/)
 * @copyright 2014-2018 Lyra Network and contributors
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Lyranetwork\Systempay\Controller\Processor;

use Lyranetwork\Systempay\Helper\Payment;

class ResponseProcessor
{

    /**
     *
     * @var \Lyranetwork\Systempay\Helper\Data
     */
    protected $dataHelper;

    /**
     *
     * @var \Lyranetwork\Systempay\Helper\Payment
     */
    protected $paymentHelper;

    /**
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     *
     * @var \Lyranetwork\Systempay\Model\Api\SystempayResponseFactory
     */
    protected $systempayResponseFactory;

    /**
     *
     * @param \Lyranetwork\Systempay\Helper\Data $dataHelper
     * @param \Lyranetwork\Systempay\Helper\Payment $paymentHelper
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Lyranetwork\Systempay\Model\Api\SystempayResponseFactory $systempayResponseFactory
     */
    public function __construct(
        \Lyranetwork\Systempay\Helper\Data $dataHelper,
        \Lyranetwork\Systempay\Helper\Payment $paymentHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Lyranetwork\Systempay\Model\Api\SystempayResponseFactory $systempayResponseFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->paymentHelper = $paymentHelper;
        $this->orderFactory = $orderFactory;
        $this->systempayResponseFactory = $systempayResponseFactory;
    }

    public function execute(\Lyranetwork\Systempay\Api\ResponseActionInterface $controller)
    {
        $request = $controller->getRequest()->getParams();

        // loading order
        $orderId = key_exists('vads_order_id', $request) ? $request['vads_order_id'] : 0;
        $order = $this->orderFactory->create();
        $order->loadByIncrementId($orderId);

        // get store id from order
        $storeId = $order->getStore()->getId();

        // load API response
        $systempayResponse = $this->systempayResponseFactory->create(
            [
                'params' => $request,
                'ctx_mode' => $this->dataHelper->getCommonConfigData('ctx_mode', $storeId),
                'key_test' => $this->dataHelper->getCommonConfigData('key_test', $storeId),
                'key_prod' => $this->dataHelper->getCommonConfigData('key_prod', $storeId),
                'algo' => $this->dataHelper->getCommonConfigData('sign_algo', $storeId)
            ]
        );

        $this->dataHelper->log($this->dataHelper->getCommonConfigData('sign_algo', $storeId));

        if (! $systempayResponse->isAuthentified()) {
            // authentification failed
            $this->dataHelper->log(
                "{$this->dataHelper->getIpAddress()} tries to access systempay/payment/response page without valid signature with parameters: " . json_encode($request),
                \Psr\Log\LogLevel::ERROR
            );

            $this->dataHelper->log(
                'Signature algorithm selected in module settings must be the same as one selected in Systempay Back Office.',
                \Psr\Log\LogLevel::ERROR
            );

            return $controller->redirectError($order);
        }

        $this->dataHelper->log("Request authenticated for order #{$order->getId()}.");

        if (! $orderId) {
            $this->dataHelper->log(
                "Order ID not returned. Payment result: " . $systempayResponse->getLogMessage(),
                \Psr\Log\LogLevel::ERROR
            );
            return $controller->redirectError($order);
        }

        if ($order->getStatus() == 'pending_payment') {
            // order waiting for payment
            $this->dataHelper->log("Order #{$order->getId()} is waiting payment.");
            $this->dataHelper->log("Payment result for order #{$order->getId()}: " . $systempayResponse->getLogMessage());

            if ($systempayResponse->isAcceptedPayment()) {
                $this->dataHelper->log("Payment for order #{$order->getId()} has been confirmed by client return !" .
                     " This means the notification URL did not work.", \Psr\Log\LogLevel::WARNING);

                // save order and optionally create invoice
                $this->paymentHelper->registerOrder($order, $systempayResponse);

                // display success page
                return $controller->redirectResponse(
                    $order,
                    Payment::SUCCESS,
                    true /* notification url warn in TEST mode */
                );
            } else {
                $this->dataHelper->log("Payment for order #{$order->getId()} has failed.");

                // cancel order
                $this->paymentHelper->cancelOrder($order, $systempayResponse);

                // redirect to cart page
                $case = $systempayResponse->isCancelledPayment() ? Payment::CANCEL : Payment::FAILURE;
                return $controller->redirectResponse($order, $case /* is success ? */);
            }
        } else {
            // payment already processed
            $this->dataHelper->log("Order #{$order->getId()} has already been processed.");

            $acceptedStatus = $this->dataHelper->getCommonConfigData('registered_order_status', $storeId);
            $successStatuses = [
            $acceptedStatus,
            'complete' /* case of virtual orders */,
            'payment_review' /* case of pending payments like Oney */,
            'fraud' /* fraud status is taken as successful because it's just a suspicion */,
            'systempay_to_validate' /* payment will be done after manual validation */
            ];

            if ($systempayResponse->isAcceptedPayment() && in_array($order->getStatus(), $successStatuses)) {
                $this->dataHelper->log("Order #{$order->getId()} is confirmed.");
                return $controller->redirectResponse($order, Payment::SUCCESS);
            } elseif ($order->isCanceled() && ! $systempayResponse->isAcceptedPayment()) {
                $this->dataHelper->log("Order #{$order->getId()} cancelation is confirmed.");

                $case = $systempayResponse->isCancelledPayment() ? Payment::CANCEL : Payment::FAILURE;
                return $controller->redirectResponse($order, $case);
            } else {
                // error case, the client returns with an error code but the payment has already been accepted
                $this->dataHelper->log(
                    "Order #{$order->getId()} has been validated but we receive a payment error code !",
                    \Psr\Log\LogLevel::ERROR
                );
                return $controller->redirectError($order);
            }
        }
    }
}
