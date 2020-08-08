<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Helper;

/**
 * Directory separator shorthand
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

use Ced\Amazon\Api\AccountRepositoryInterface;
use Ced\Amazon\Api\FeedRepositoryInterface;
use Ced\Amazon\Api\Order\ItemRepositoryInterface;
use Ced\Amazon\Api\OrderRepositoryInterface;
use Ced\Amazon\Api\QueueRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Shipment
 * @package Ced\Amazon\Helper
 */
class Shipment
{
    /** @var AccountRepositoryInterface */
    public $account;

    /** @var FeedRepositoryInterface */
    public $feed;

    /** @var ItemRepositoryInterface  */
    public $itemRepository;

    /** @var \Ced\Amazon\Model\OrderFactory */
    public $order;

    /** @var OrderRepositoryInterface  */
    public $orderRepository;

    /** @var \Ced\Amazon\Service\Config */
    public $config;

    /** @var Logger */
    public $logger;

    /** @var \Amazon\Sdk\Envelope */
    public $envelope;

    /** @var \Amazon\Sdk\Validator */
    public $validator;

    /** @var \Amazon\Sdk\Api\Order */
    public $api;

    /** @var \Amazon\Sdk\Order\FulfillmentFactory */
    public $fulfillment;

    /**
     * @var QueueRepositoryInterface
     */
    public $queue;

    /** @var \Ced\Amazon\Api\Data\Queue\DataInterfaceFactory */
    public $queueDataFactory;

    public function __construct(
        ItemRepositoryInterface $itemRepository,
        AccountRepositoryInterface $account,
        FeedRepositoryInterface $feed,
        QueueRepositoryInterface $queue,
        OrderRepositoryInterface $orderRepository,
        \Ced\Amazon\Model\OrderFactory $orderFactory,
        \Ced\Amazon\Api\Data\Queue\DataInterfaceFactory $queueDataFactory,
        \Ced\Amazon\Service\Config $config,
        \Ced\Amazon\Helper\Logger $logger,
        \Amazon\Sdk\Order\FulfillmentFactory $fulfillment,
        \Amazon\Sdk\EnvelopeFactory $envelope,
        \Amazon\Sdk\ValidatorFactory $validator,
        \Amazon\Sdk\Api\OrderFactory $api
    ) {
        $this->account = $account;
        $this->feed = $feed;
        $this->order = $orderFactory;
        $this->config = $config;
        $this->logger = $logger;
        $this->queue = $queue;
        $this->queueDataFactory = $queueDataFactory;
        $this->itemRepository = $itemRepository;
        $this->orderRepository = $orderRepository;

        $this->api = $api;
        $this->fulfillment = $fulfillment;
        $this->envelope = $envelope;
        $this->validator = $validator;
    }

    public function sync($orderId, $shipmentId)
    {
        $shipment = $this->get($orderId, $shipmentId);
        if (isset($shipment['feed_id'])) {
            $status = $this->feed->sync($shipment['feed_id']);

            if ($status == false) {
                /** @var \Ced\Amazon\Model\Order $order */
                $order = $this->orderRepository->getById($orderId);
                $response = $this->feed->getResultByFeedId(
                    $shipment['feed_id'],
                    $order->getData(\Ced\Amazon\Model\Order::COLUMN_ACCOUNT_ID)
                );
                if (!empty($response) && strpos($response, '<StatusCode>Complete</StatusCode>') !== false) {
                    $status = \Ced\Amazon\Model\Source\Feed\Status::DONE;
                }
            }

            $this->update($orderId, $shipmentId, ['Status' => $status]);
        }

        return $shipment;
    }

    /**
     * Get all shipments
     * @param $orderId
     * @param null $shipmentId
     * @param \Ced\Amazon\Model\Order|null $order
     * @return array|mixed
     */
    public function get($orderId, $shipmentId = null, $order = null)
    {
        $shipments = [];
        if (!isset($order)) {
            /** @var \Ced\Amazon\Model\Order $order */
            $order = $this->orderRepository->getById($orderId);
        }

        if (!empty($order) && $order->getId() > 0) {
            $shipments = $order->getData(\Ced\Amazon\Model\Order::COLUMN_SHIPMENT_DATA);
            $shipments = !empty($shipments) ? json_decode($shipments, true) : [];
            if (isset($shipmentId)) {
                if (isset($shipments[$shipmentId])) {
                    $shipments = $shipments[$shipmentId];
                } else {
                    $shipments = [];
                }
            }
        }

        return $shipments;
    }

    /**
     * Update shipment status
     * @param int $orderId, Amazon Order Row Id
     * @param int $shipmentId, Magento Shipment Id
     * @param array $data
     * @throws \Exception
     */
    public function update($orderId, $shipmentId, array $data = [])
    {
        /** @var \Ced\Amazon\Model\Order $order */
        $order = $this->orderRepository->getById($orderId);
        if (!empty($order) && $order->getId() > 0) {
            $shipments = $order->getData(\Ced\Amazon\Model\Order::COLUMN_SHIPMENT_DATA);
            $shipments = !empty($shipments) ? json_decode($shipments, true) : [];
            if (isset($shipmentId, $shipments[$shipmentId])) {
                $shipments[$shipmentId] = array_merge($shipments[$shipmentId], $data);
                $order->setData(\Ced\Amazon\Model\Order::COLUMN_SHIPMENT_DATA, json_encode($shipments));
                $this->orderRepository->save($order);
            }
        }
    }

    /**
     * Delete shipment form mp shipments
     * @param $orderId
     * @param null $shipmentId
     * @param \Ced\Amazon\Model\Order null $order
     * @return bool
     * @throws \Exception
     */
    public function delete($orderId, $shipmentId = null, $order = null)
    {
        $status = false;
        if (!isset($order)) {
            /** @var \Ced\Amazon\Model\Order $order */
            $order = $this->orderRepository->getById($orderId);
        }

        if (!empty($order) && $order->getId() > 0) {
            $shipments = $order->getData(\Ced\Amazon\Model\Order::COLUMN_SHIPMENT_DATA);
            $shipments = !empty($shipments) ? json_decode($shipments, true) : [];
            if (isset($shipmentId, $shipments[$shipmentId])) {
                unset($shipments[$shipmentId]);
                $order->setData(\Ced\Amazon\Model\Order::COLUMN_SHIPMENT_DATA, json_encode($shipments));
                $this->orderRepository->save($order);
                $status = true;
            }
        }

        return $status;
    }

    /**
     * Create Shipment on Amazon using Magento Shipment Object
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     */
    public function create($shipment)
    {
        $orderData = [];
        $orderItems = [];
        $incrementId = '';
        $poId = '';
        try {
            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
            if (!empty($shipment)) {
                /** @var \Magento\Sales\Model\Order $order */
                $order = $shipment->getOrder();
                $incrementId = $order->getIncrementId();
                $orderId = $order->getId();
                /** @var \Ced\Amazon\Model\Order $mporder */
                $mporder = $this->orderRepository->getByOrderId($orderId);

                if (isset($mporder) && !empty($mporder->getData(\Ced\Amazon\Model\Order::COLUMN_PO_ID))) {
                    $poId = $mporder->getData(\Ced\Amazon\Model\Order::COLUMN_PO_ID);
                    $orderData = $mporder->getData(\Ced\Amazon\Model\Order::COLUMN_ORDER_DATA);
                    $orderData = !empty($orderData) ? json_decode($orderData, true) : [];
                    $orderItems = $mporder->getData(\Ced\Amazon\Model\Order::COLUMN_ORDER_ITEMS);
                    $orderItems = !empty($orderItems) ? json_decode($orderItems, true) : [];

                    $fulfillments = $mporder->getData(\Ced\Amazon\Model\Order::COLUMN_SHIPMENT_DATA);
                    $fulfillments = !empty($fulfillments) ? json_decode($fulfillments, true) : [];
                    $this->logger->info(
                        'Shipment creation started via shipment helper.',
                        [
                            'po_id' => $poId,
                            'increment_id' => $incrementId,
                            'shipment_data' => $shipment->getData(),
                            'mp_order_data' => $orderData,
                            'mp_order_items' => $orderItems,
                            'mp_shipment_data' => $fulfillments,
                            'path' => __METHOD__
                        ]
                    );

                    /** @var \Magento\Sales\Api\Data\ShipmentItemInterface[] $items */
                    $items = $shipment->getAllItems();
                    $tracks = $shipment->getAllTracks();
                    $trackingRequired = $this->config->isTrackingNumberRequired();

                    /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
                    foreach ($tracks as $track) {
                        if (empty($track->getData('track_number')) && $trackingRequired) {
                            continue;
                        }

                        $this->logger->info(
                            'Track processing started via shipment helper.',
                            [
                                'po_id' => $poId,
                                'increment_id' => $incrementId,
                                'track_data' => $track->getData(),
                                'path' => __METHOD__
                            ]
                        );

                        $title = $track->getData('title');
                        $code = $track->getData('carrier_code');
                        $allowedCode = $this->getCarrierCode($code, $title);
                        if (empty($allowedCode)) {
                            $carrierName = $code;
                            $carrierCode = "";
                        } else {
                            $carrierCode = $allowedCode;
                            $carrierName = "";
                        }

                        $data = [
                            'OrderId' => $orderId,
                            'TrackId' => $track->getId(),
                            'IncrementId' => $incrementId,

                            'AmazonOrderID' => $poId,
                            'FulfillmentDate' => (string)$this->getDate($shipment->getData('created_at')),
                            'FulfillmentData' => [
                                'CarrierCode' => $carrierCode,
                                'CarrierName' => $carrierName,
                                'ShippingMethod' => $track->getData('title'),
                                'ShipperTrackingNumber' => $track->getData('track_number'),
                            ],
                            'Items' => [

                            ]
                        ];

                        foreach ($items as $item) {
                            $data['Items'][] = [
                                'SKU' => (string)$item->getSku(),
                                'AmazonOrderItemCode' => (string)$this->getOrderItemCode(
                                    $item,
                                    $orderItems,
                                    $orderData
                                ),
                                'Quantity' => (string)(int)$item->getQty(),
                            ];
                        }

                        $specifics = [
                            'ids' => [$shipment->getId()],
                            'data' => $data,
                            'account_id' => $mporder->getData(\Ced\Amazon\Model\Order::COLUMN_ACCOUNT_ID),
                            'marketplace' => $mporder->getData(\Ced\Amazon\Model\Order::COLUMN_MARKETPLACE_ID),
                            'profile_id' => null,
                            'store_id' => $shipment->getStoreId(),
                            'type' => \Amazon\Sdk\Api\Feed::ORDER_FULFILLMENT,
                        ];
                        $async = $this->config->getShipmentMode();
                        if ($async) {
                            $r = $this->queue($specifics);
                            $status = isset($r[0]) ? $r[0] : false;
                            $queues = isset($r[1]) ? $r[1] : [];

                            $specifics['data']['shipment_id'] = $shipment->getId();
                            $specifics['data']['queue_ids'] = implode(",", $queues);
                            $specifics['data']['errors'] = !$status;
                            $specifics['data']['Feed'] = [];
                            $specifics['data']['Status'] = \Ced\Amazon\Model\Source\Feed\Status::NOT_SUBMITTED;
                            $this->add($shipment->getId(), $specifics['data'], $mporder);
                        } else {
                            $envelope = $this->prepare($specifics);
                            $feed = $this->feed->send($envelope, $specifics);

                            $error = '';
                            if (empty($envelope)) {
                                $error = 'Shipment prepare failed.';
                            }

                            $specifics['data']['shipment_id'] = $shipment->getId();
                            //TODO: find a way to set errors in shipment
                            $specifics['data']['errors'] = $error;
                            $specifics['data']['Feed'] = $feed;
                            if (isset($feed['Id'])) {
                                $specifics['data']['feed_id'] = $feed['Id'];
                                $specifics['data']['Status'] = \Ced\Amazon\Model\Source\Feed\Status::SUBMITTED;
                            } else {
                                $specifics['data']['feed_id'] = '0';
                                $specifics['data']['Status'] = \Ced\Amazon\Model\Source\Feed\Status::FAILED;
                            }

                            $this->add($shipment->getId(), $specifics['data'], $mporder);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical(
                'Shipment create observer failed.',
                [
                    'exception' => $e->getMessage(),
                    'po_id' => $poId,
                    'increment_id' => $incrementId,
                    'order_data' => $orderData,
                    'order_items' => $orderItems,
                    'path' => __METHOD__
                ]
            );
        }
    }

    /**
     * Get Carrier Code for Amazon
     * @param string $code
     * @param string $title
     * @return mixed|string
     */
    private function getCarrierCode($code = "", $title = "")
    {
        $value = "";
        $mappings = [
            'usps' => "USPS",
            'fedex' => "FedEx",
            'dhl' => "DHL",
        ];

        if ($code == "custom") {
            $code = $title;
        }

        if (!empty($code)) {
            $result = \Amazon\Sdk\Order\Fulfillment\CarrierCode::search($code);
            if (isset($result)) {
                $value = $result;
            }
        } elseif (isset($mappings[$code]) && !empty($mappings[$code])) {
            $value = $mappings[$code];
        } else {
            $value = "";
        }

        return $value;
    }

    private function getDate($date)
    {
        $result = date('Y-m-d H:i:s P', strtotime($date));
        return $result;
    }

    /**
     * Get Order Item Code
     * @param \Magento\Sales\Api\Data\ShipmentItemInterface $item
     * @param array $orderItems
     * @param array $orderData
     * @return string
     */
    private function getOrderItemCode($item, array $orderItems = [], array $orderData = [])
    {
        $orderItemCode = '0';
        try {
            $magentoItemId = $item->getOrderItemId();
            /** @var \Ced\Amazon\Model\Order\Item $amazonItem */
            $amazonItem = $this->itemRepository->getByMagentoOrderItemId($magentoItemId);
            $orderItemCode = $amazonItem->getOrderItemId();
        } catch (LocalizedException $e) {
            $sku = $item->getSku();
            if (!empty($sku) && !empty($orderItems) && !empty($orderData)) {
                foreach ($orderItems as $orderItem) {
                    if (isset($orderItem['OrderItemId'], $orderItem['SellerSKU'])
                        && $sku == $orderItem['SellerSKU']) {
                        $orderItemCode = $orderItem['OrderItemId'];
                        break;
                    }
                }
            }
        }

        return $orderItemCode;
    }

    /**
     * Prepare shipment array
     * @param array $specifics
     * @param \Amazon\Sdk\Envelope $envelope
     * @return \Amazon\Sdk\Envelope|null $envelope
     */
    public function prepare(array $specifics = [], $envelope = null)
    {
        if (isset($specifics) && !empty($specifics)) {
            try {
                /** @var \Amazon\Sdk\Order\Fulfillment $fulfillment */
                $fulfillment = $this->fulfillment->create();

                if (isset($specifics['data']['OrderId']) && !empty($specifics['data']['OrderId'])) {
                    /** @var int $orderId, Magento Order Entity Id */
                    $orderId = $specifics['data']['OrderId'];

                    // Adding unique message id
                    $messageId = (string)$specifics['data']['OrderId'];
                    if (isset($specifics['data']['TrackId'])) {
                        $messageId .= (string)$specifics['data']['TrackId'];
                    }
                    $fulfillment->setId($messageId);

                    // Saving fulfillment data.
                    /** @var \Ced\Amazon\Model\Order $mporder */
                    $mporder = $this->orderRepository->getByOrderId($orderId);

                    /** @var \Ced\Amazon\Model\Account $account */
                    $account = $this->account->getById($mporder->getData(\Ced\Amazon\Model\Order::COLUMN_ACCOUNT_ID));
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Order Id is invalid.'));
                }

                if (isset($specifics['data']['AmazonOrderID']) && !empty($specifics['data']['AmazonOrderID'])) {
                    $fulfillment->setData($specifics['data']['AmazonOrderID'], $specifics['data']);
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(__('AmazonOrderID is invalid.'));
                }

                if (isset($specifics['data']['Items']) && !empty($specifics['data']['Items'])) {
                    $fulfillment->setItems($specifics['data']['Items']);
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Items are missing.'));
                }

                /** @var \Amazon\Sdk\Validator $validator */
                $validator = $this->validator->create(
                    ['object' => $fulfillment]
                );

                // $validator->validate(), TODO: fix validator
                if (true) {
                    if (!isset($envelope)) {
                        $envelope = $this->envelope->create(
                            [
                                'merchantIdentifier' => $account->getConfig()->getSellerId(),
                                'messageType' => \Amazon\Sdk\Base::MESSAGE_TYPE_ORDER_FULFILLMENT
                            ]
                        );
                    }

                    $envelope->addFulfillment($fulfillment);
                } else {
                    $this->logger->critical(
                        'Prepare shipment failed due to invalid data.',
                        [
                            'specifics' => $specifics,
                            'errors' => $validator->getErrors(),
                            'path' => __METHOD__
                        ]
                    );
                }
            } catch (\Exception $exception) {
                $this->logger->critical(
                    'Prepare shipment failed.' . $exception->getMessage(),
                    [
                        'exception' => $exception->getMessage(),
                        'specifics' => $specifics,
                        'path' => __METHOD__
                    ]
                );
            }
        }

        return $envelope;
    }

    /**
     * Add a shipment
     * @param string $id, TODO: Use track id, instead of shipment id.
     * @param array $shipment
     * @param \Ced\Amazon\Model\Order|null $order
     * @throws \Exception
     */
    public function add($id = null, $shipment = [], \Ced\Amazon\Model\Order $order = null)
    {
        // Sync order while adding shipment from Amazon: 1 call per min allowed, hence disabled.
        $sync = false;

        if (isset($order, $id) && !empty($shipment)) {
            $shipments = $order->getData(\Ced\Amazon\Model\Order::COLUMN_SHIPMENT_DATA);
            $shipments = !empty($shipments) ? json_decode($shipments, true) : [];
            if ($id == 'na') {
                $shipments[] = $shipment;
            } else {
                $shipments[$id] = $shipment;
            }

            /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
            $account = $this->account->getById($order->getData(\Ced\Amazon\Model\Order::COLUMN_ACCOUNT_ID));
            $config = $account->getConfig();
            $poId = $order->getData(\Ced\Amazon\Model\Order::COLUMN_PO_ID);
            try {
                // Updating order_data after shipment
                // Adding OrderId Parameter: Optional, Should be applied in the last.
                if (isset($poId) && !empty($poId) && $sync) {
                    /** @var \Amazon\Sdk\Api\Order $api */
                    $api = $this->api->create([
                        'config' => $config,
                        'logger' => $this->logger,
                        'mockMode' => $account->getMockMode(),
                    ]);

                    $api->setOrderId($poId);
                    $api->fetchOrder();
                    $data = $api->getData();
                }

                if (!empty($data)) {
                    $order->setData(
                        \Ced\Amazon\Model\Order::COLUMN_ORDER_DATA,
                        json_encode($data)
                    );
                }
            } catch (\Exception $e) {
                $this->logger->addCritical(
                    'Order update failed after shipment.',
                    [
                        'exception' => $e->getMessage(),
                        'po_id' => $poId,
                        'shipment_id' => $id,
                        'path' => __METHOD__
                    ]
                );
            }

            $order->setData(\Ced\Amazon\Model\Order::COLUMN_SHIPMENT_DATA, json_encode($shipments));
            $this->orderRepository->save($order);
        }
    }

    public function queue(array $specifics = [])
    {
        /** @var \Ced\Amazon\Api\Data\Queue\DataInterface $queueData */
        $queueData = $this->queueDataFactory->create();
        $queueData->setAccountId($specifics['account_id']);
        $queueData->setMarketplace($specifics['marketplace']);
        $queueData->setSpecifics($specifics);
        $queueData->setOperationType(\Amazon\Sdk\Base::OPERATION_TYPE_UPDATE);
        $queueData->setType($specifics['type']);
        $status = $this->queue->push($queueData);
        $queues = $this->queue->getIds();

        return [$status, $queues];
    }
}
