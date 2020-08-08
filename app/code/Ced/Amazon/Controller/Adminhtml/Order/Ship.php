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
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Order;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

class Ship extends \Magento\Backend\App\Action
{
    /** @var \Magento\Framework\Serialize\SerializerInterface */
    public $serializer;

    public $shipment;

    public $order;

    public $orderRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Ced\Amazon\Helper\Shipment $shipment,
        \Ced\Amazon\Repository\Order $orderRepository,
        \Ced\Amazon\Helper\Order $order
    )
    {
        parent::__construct($context);
        $this->serializer = $serializer;
        $this->shipment = $shipment;

        $this->order = $order;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $response = [
            'message' => [],
            'success' => false
        ];

        /** @var array $data */
        $data = $this->getRequest()->getParams();
        /** @var int $orderId */
        $orderId = $this->getRequest()->getParam('order_id');

        // cleaning data
        if (isset($data['form_key'])) {
            unset($data['form_key']);
        }

        if (isset($data['key'])) {
            unset($data['key']);
        }

        if (isset($data['isAjax'])) {
            unset($data['isAjax']);
        }

        if (!empty($orderId)) {
            try {
                /** @var \Ced\Amazon\Model\Order|null $order */
                $order = $this->orderRepository->getById($orderId);
            } catch (\Exception $e) {
                $order = null;
            }
        }

        if (isset($data['fulfillments'], $order) && is_array($data['fulfillments'])) {
            $envelope = null;
            foreach ($data['fulfillments'] as $item) {
                $specifics = [
                    'ids' => ['na'],
                    'data' => $item,
                    'account_id' => $order->getData(\Ced\Amazon\Model\Order::COLUMN_ACCOUNT_ID),
                    'marketplace' => $order->getData(\Ced\Amazon\Model\Order::COLUMN_MARKETPLACE_ID),
                    'profile_id' => null,
                    'store_id' => 0,
                    'type' => \Amazon\Sdk\Api\Feed::ORDER_FULFILLMENT,
                ];
                $envelope = $this->shipment->prepare($specifics, $envelope);
                if (isset($envelope)) {
                    $feed = $this->shipment->feed->send($envelope, $specifics);
                    $specifics['data']['shipment_id'] = 'na';
                    $specifics['data']['errors'] = '';
                    $specifics['data']['Feed'] = $feed;
                    if (isset($feed['Id'])) {
                        $specifics['data']['feed_id'] = $feed['Id'];
                        $specifics['data']['Status'] = \Ced\Amazon\Model\Source\Feed\Status::SUBMITTED;
                    } else {
                        $specifics['data']['feed_id'] = '0';
                        $specifics['data']['Status'] = \Ced\Amazon\Model\Source\Feed\Status::FAILED;
                    }

                    $this->shipment->add('na', $specifics['data'], $order);

                    $response['message'][] = 'Order shipment sent successfully.';
                    $response['success'] = true;
                } else {
                    $response['message'][] = 'Shipment prepare failed.';
                }
            }
        }

        if (isset($data['adjustments'], $order) && is_array($data['adjustments'])) {
            foreach ($data['adjustments'] as $item) {
                $adjustment = $this->order->adjust($item);
                if ($adjustment['success'] === true) {
                    $response['message'][] = 'Order adjustment sent successfully.';
                    $response['success'] = true;
                } else {
                    $response['success'] = false;
                    $response['message'][] = $adjustment['message'];
                }
            }
        }

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $result->setData($response);
        return $result;
    }
}
