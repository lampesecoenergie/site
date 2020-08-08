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
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Cron\Order;

class Sync
{
    public $logger;
    public $collectionFactory;
    public $orderInterface;
    public $config;

    /**
     * Import constructor.
     *
     * @param \Ced\Cdiscount\Helper\Order $order
     * @param \Ced\Cdiscount\Helper\Logger $logger
     */
    public function __construct(
        \Ced\Cdiscount\Helper\Order $order,
        \Ced\Cdiscount\Helper\Logger $logger,
        \Magento\Sales\Api\OrderRepositoryInterface $orderInterface,
        \Ced\Cdiscount\Helper\Config $config,
        \Ced\Cdiscount\Model\ResourceModel\Orders\CollectionFactory $collectionFactory
    ) {
        $this->order = $order;
        $this->logger = $logger;
        $this->orderInterface = $orderInterface;
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
    }


    public function execute()
    {
        try {
            $orderData = $this->collectionFactory->create()
                ->addFieldToFilter('status', ['eq' => 'imported'])
                ->addFieldToSelect('cdiscount_order_id')
                ->addFieldToSelect('magento_order_id')->getData();
            if (isset($orderData) && !empty($orderData)) {
                foreach ($orderData as $orderDatum) {
                    $magentoOrderId = $orderDatum['magento_order_id'];
                    $cdiscountOrderId = $orderDatum['cdiscount_order_id'];
                    $trackArray = [];
                    $data = [];
                    $tracksCollection = $this->orderInterface->get($magentoOrderId)->getTracksCollection()->getItems();
                    foreach ($tracksCollection as $track) {
                        $trackArray = $track->getData();
                    }
                    if ($this->orderInterface->get($magentoOrderId)->getStatus() == 'complete') {
                        if (isset($trackArray['track_number']) && !empty($cdiscountOrderId)) {
                            $data['OrderId'] = $magentoOrderId;
                            $data['CdiscountOrderID'] = $cdiscountOrderId;
                            $data['ShippingProvider'] = $trackArray['title'];
                            $data['TrackingNumber'] = $trackArray['track_number'];
                            if (isset($data) && !empty($data)) {
                                $this->order->shipOrder($data, true);
                                $this->logger->error('Ship By Cron',
                                    ['path' => __METHOD__, 'data' => $data]);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            if ($this->config->getDebugMode() == true) {
                $this->logger->error($exception->getMessage(),
                    ['path' => __METHOD__, 'trace' => $exception->getTraceAsString()]);
            }
        }
    }
}
