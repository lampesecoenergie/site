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

use \Lyranetwork\Systempay\Model\Api\SystempayApi;

class CheckProcessor
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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

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
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Lyranetwork\Systempay\Helper\Data $dataHelper
     * @param \Lyranetwork\Systempay\Helper\Payment $paymentHelper
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Lyranetwork\Systempay\Model\Api\SystempayResponseFactory $systempayResponseFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Lyranetwork\Systempay\Helper\Data $dataHelper,
        \Lyranetwork\Systempay\Helper\Payment $paymentHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Lyranetwork\Systempay\Model\Api\SystempayResponseFactory $systempayResponseFactory
    ) {
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $this->paymentHelper = $paymentHelper;
        $this->orderFactory = $orderFactory;
        $this->systempayResponseFactory = $systempayResponseFactory;
    }

    public function execute(\Lyranetwork\Systempay\Api\CheckActionInterface $controller)
    {
        if (! $controller->getRequest()->isPost()) {
            return;
        }

        $post = $controller->getRequest()->getParams();

        // loading order
        $orderId = key_exists('vads_order_id', $post) ? $post['vads_order_id'] : 0;
        $order = $this->orderFactory->create();
        $order->loadByIncrementId($orderId);

        // get store id from order
        $storeId = $order->getStore()->getId();

        // init app with correct store id
        $this->storeManager->setCurrentStore($storeId);

        // load API response
        $systempayResponse = $this->systempayResponseFactory->create(
            [
                'params' => $post,
                'ctx_mode' => $this->dataHelper->getCommonConfigData('ctx_mode', $storeId),
                'key_test' => $this->dataHelper->getCommonConfigData('key_test', $storeId),
                'key_prod' => $this->dataHelper->getCommonConfigData('key_prod', $storeId),
                'algo' => $this->dataHelper->getCommonConfigData('sign_algo', $storeId)
            ]
        );

        if (! $systempayResponse->isAuthentified()) {
            // authentification failed
            $this->dataHelper->log(
                "{$this->dataHelper->getIpAddress()} tries to access systempay/payment/check page without valid signature with parameters: " . json_encode($post),
                \Psr\Log\LogLevel::ERROR
            );

            $this->dataHelper->log(
                'Signature algorithm selected in module settings must be the same as one selected in Systempay Back Office.',
                \Psr\Log\LogLevel::ERROR
            );

            return $controller->renderResponse($systempayResponse->getOutputForPlatform('auth_fail'));
        }

        $this->dataHelper->log("Request authenticated for order #{$order->getId()}.");

        $reviewStatuses = [
            'payment_review',
            'systempay_to_validate',
            'fraud'
        ];

        if ($order->getStatus() == 'pending_payment' || in_array($order->getStatus(), $reviewStatuses)) {
            // order waiting for payment
            $this->dataHelper->log("Order #{$order->getId()} is waiting payment update.");
            $this->dataHelper->log("Payment result for order #{$order->getId()}: " . $systempayResponse->getLogMessage());

            if ($systempayResponse->isAcceptedPayment()) {
                $this->dataHelper->log("Payment for order #{$order->getId()} has been confirmed by notification URL.");

                $stateObject = $this->paymentHelper->nextOrderState($order, $systempayResponse);
                if ($order->getStatus() == $stateObject->getStatus()) {
                    // payment status is unchanged display notification url confirmation message
                    return $controller->renderResponse($systempayResponse->getOutputForPlatform('payment_ok_already_done'));
                } else {
                    // save order and optionally create invoice
                    $this->paymentHelper->registerOrder($order, $systempayResponse);

                    // display notification url confirmation message
                    return $controller->renderResponse($systempayResponse->getOutputForPlatform('payment_ok'));
                }
            } else {
                $this->dataHelper->log("Payment for order #{$order->getId()} has been invalidated by notification URL.");

                // cancel order
                $this->paymentHelper->cancelOrder($order, $systempayResponse);

                // display notification url failure message
                return $controller->renderResponse($systempayResponse->getOutputForPlatform('payment_ko'));
            }
        } else {
            // payment already processed

            $acceptedStatus = $this->dataHelper->getCommonConfigData('registered_order_status', $storeId);
            $successStatuses = [
                $acceptedStatus,
                'complete' /* case of virtual orders */
            ];

            if ($systempayResponse->isAcceptedPayment() && in_array($order->getStatus(), $successStatuses)) {
                $this->dataHelper->log("Order #{$order->getId()} is confirmed.");

                if ($systempayResponse->get('operation_type') == 'CREDIT') {
                    // this is a refund: create credit memo ?

                    $expiry = '';
                    if ($systempayResponse->get('expiry_month') && $systempayResponse->get('expiry_year')) {
                        $expiry = str_pad($systempayResponse->get('expiry_month'), 2, '0', STR_PAD_LEFT) . ' / ' .
                             $systempayResponse->get('expiry_year');
                    }

                    $transactionId = $systempayResponse->get('trans_id') . '-' . $systempayResponse->get('sequence_number');

                    // save paid amount
                    $currency = SystempayApi::findCurrencyByNumCode($systempayResponse->get('currency'));
                    $amount = round(
                        $currency->convertAmountToFloat($systempayResponse->get('amount')),
                        $currency->getDecimals()
                    );

                    $amountDetail = $amount . ' ' . $currency->getAlpha3();

                    if ($systempayResponse->get('effective_currency') &&
                         ($systempayResponse->get('currency') !== $systempayResponse->get('effective_currency'))) {
                        $effectiveCurrency = SystempayApi::findCurrencyByNumCode($systempayResponse->get('effective_currency'));

                        $effectiveAmount = round(
                            $effectiveCurrency->convertAmountToFloat($systempayResponse->get('effective_amount')),
                            $effectiveCurrency->getDecimals()
                        );

                        $amountDetail = $effectiveAmount . ' ' . $effectiveCurrency->getAlpha3() . ' (' . $amountDetail . ')';
                    }

                    $additionalInfo = [
                        'Transaction Type' => 'CREDIT',
                        'Amount' => $amountDetail,
                        'Transaction ID' => $transactionId,
                        'Transaction UUID' => $systempayResponse->get('trans_uuid'),
                        'Transaction Status' => $systempayResponse->get('trans_status'),
                        'Means of payment' => $systempayResponse->get('card_brand'),
                        'Card Number' => $systempayResponse->get('card_number'),
                        'Expiration Date' => $expiry,
                        '3DS Certificate' => ''
                    ];

                    $transactionType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND;

                    $this->paymentHelper->addTransaction(
                        $order->getPayment(),
                        $transactionType,
                        $transactionId,
                        $additionalInfo
                    );
                } else {
                    // update transaction info
                    $this->paymentHelper->updatePaymentInfo($order, $systempayResponse);
                }

                $order->save();

                return $controller->renderResponse($systempayResponse->getOutputForPlatform('payment_ok_already_done'));
            } elseif ($order->isCanceled() && ! $systempayResponse->isAcceptedPayment()) {
                $this->dataHelper->log("Order #{$order->getId()} cancelation is confirmed.");
                return $controller->renderResponse($systempayResponse->getOutputForPlatform('payment_ko_already_done'));
            } else {
                // error case, the client returns with an error code but the payment already has been accepted
                $this->dataHelper->log(
                    "Order #{$order->getId()} has been validated but we receive a payment error code !",
                    \Psr\Log\LogLevel::ERROR
                );
                return $controller->renderResponse($systempayResponse->getOutputForPlatform('payment_ko_on_order_ok'));
            }
        }
    }
}
