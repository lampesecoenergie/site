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
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Service;

use Amazon\Sdk\Api\ConfigFactory as AmazonApiConfigFactory;
use Amazon\Sdk\Api\Order\ItemListFactory as AmazonApiOrderItemListFactory;
use Amazon\Sdk\Api\Order\OrderListFactory as AmazonApiOrderListFactory;
use Amazon\Sdk\Api\OrderFactory as AmazonApiOrderFactory;
use Ced\Amazon\Api\Data\Order\Import\ParamsInterface;
use Ced\Amazon\Api\Data\Order\Import\ResultInterface;
use Ced\Amazon\Api\Data\Order\Import\ResultInterfaceFactory;
use Ced\Amazon\Api\Service\ConfigServiceInterface;
use Ced\Amazon\Api\Service\CustomerServiceInterface;
use Ced\Amazon\Api\Service\OrderServiceInterface;
use Ced\Amazon\Api\Service\QuoteServiceInterface;
use Ced\Amazon\Helper\Logger;
use Ced\Amazon\Model\Source\Order\Failure\Reason;
use Ced\Amazon\Repository\Account as AccountRepository;
use Ced\Amazon\Repository\Order as AmazonOrderRepository;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as MagentoOrderCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Order
 * @package Ced\Amazon\Service
 * Use Registry/Order for store data during transaction
 */
class Order implements OrderServiceInterface
{
    use Common;

    /** @var SerializerInterface  */
    public $serializer;

    /** @var MagentoOrderCollectionFactory  */
    public $magentoOrderCollectionFactory;

    /** @var AccountRepository */
    public $accountRepository;

    /** @var AmazonOrderRepository  */
    public $amazonOrderRepository;

    /** @var StoreManagerInterface */
    public $storeManager;

    /** @var RegionFactory */
    public $regionFactory;

    /** @var AmazonApiOrderListFactory */
    public $orderListApi;

    /** @var AmazonApiOrderFactory */
    public $orderApi;

    /** @var Logger */
    public $logger;

    /** @var ConfigServiceInterface  */
    public $config;

    /** @var CustomerServiceInterface  */
    public $customer;

    /** @var QuoteServiceInterface  */
    public $quote;

    /** @var ResultInterfaceFactory  */
    public $resultFactory;

    /** @var ResultInterface */
    public $result;

    /** @var AmazonApiOrderFactory  */
    public $amazonApiOrderFactory;

    /** @var AmazonApiOrderItemListFactory  */
    public $amazonApiItemListFactory;

    /** @var AmazonApiConfigFactory  */
    public $amazonApiConfigFactory;

    /** @var int  */
    public $imported = 0;

    private $regions = [];

    public function __construct(
        SerializerInterface $serializer,
        StoreManagerInterface $storeManager,
        RegionFactory $regionFactory,
        MagentoOrderCollectionFactory $magentoOrderCollectionFactory,
        AccountRepository $accountRepository,
        AmazonOrderRepository $orderRepository,
        AmazonApiOrderFactory $amazonOrderFactory,
        AmazonApiOrderListFactory $amazonOrderListFactory,
        ConfigServiceInterface $config,
        CustomerServiceInterface $customer,
        QuoteServiceInterface $quote,
        AmazonApiOrderFactory $amazonApiOrderFactory,
        AmazonApiOrderItemListFactory $amazonApiItemListFactory,
        AmazonApiConfigFactory $amazonApiConfigFactory,
        Logger $logger,
        ResultInterfaceFactory $resultFactory
    ) {
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
        $this->regionFactory = $regionFactory;
        $this->magentoOrderCollectionFactory = $magentoOrderCollectionFactory;
        $this->accountRepository = $accountRepository;
        $this->amazonOrderRepository = $orderRepository;
        $this->orderApi = $amazonOrderFactory;
        $this->orderListApi = $amazonOrderListFactory;
        $this->logger = $logger;
        $this->config = $config;
        $this->customer = $customer;
        $this->quote = $quote;
        $this->resultFactory = $resultFactory;
        $this->amazonApiOrderFactory = $amazonApiOrderFactory;
        $this->amazonApiItemListFactory = $amazonApiItemListFactory;
        $this->amazonApiConfigFactory = $amazonApiConfigFactory;
    }

    /**
     * Import Order into Magento
     * @param ParamsInterface $params
     * @return ResultInterface
     */
    public function import(ParamsInterface $params)
    {
        /** @var ResultInterface $result */
        $this->result = $this->resultFactory->create();
        $this->result->setParams($params);
        try {
            $total = 0;
            $this->imported = 0;
            $this->processTaxRegions();

            /** @var \Ced\Amazon\Api\Data\AccountSearchResultsInterface $accounts */
            $accounts = $this->accountRepository->getAvailableList($params->getAccountIds());

            \Magento\Framework\Profiler::start('ced-amazon-order-import');

            /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
            foreach ($accounts->getItems() as $account) {
                /** @var int $storeId */
                $storeId = $account->getStore();
                /** @var \Magento\Store\Api\Data\StoreInterface $store */
                $store = $this->storeManager->getStore($storeId);

                $orders = $this->load($params, $account);

                $total += is_array($orders) ? count($orders) : 0;

                // Process orders
                $this->process($orders, $account, $store, $params->getCreate(), $params->getCliLimit());
            }

            \Magento\Framework\Profiler::stop('ced-amazon-order-import');

            $this->result->setOrderTotal($total);
            $this->result->setOrderImportedTotal($this->imported);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['path' => __METHOD__]);
        }

        return $this->result;
    }

    /**
     * Load Order from Amazon
     * @param ParamsInterface $params
     * @param \Ced\Amazon\Api\Data\AccountInterface $account
     * @return array
     * @throws \Amazon\Sdk\Api\Exception\InvalidConfigValue
     */
    private function load($params, $account)
    {
        $orders = [];
        $log = [
            "account_name" => $account->getName(),
            "type" => $params->getType(),
        ];
        /** @var \Amazon\Sdk\Api\Config $config */
        $config = $account->getConfig();
        if ($params->getMode() == \Ced\Amazon\Api\Data\Order\Import\ParamsInterface::IMPORT_MODE_API) {
            $orderId = $params->getAmazonOrderId();
            if (isset($orderId) && !empty($orderId)) {
                if (is_array($orderId)) {
                    /** @var \Amazon\Sdk\Api\Order\OrderList $orderList */
                    $orderList = $this->orderListApi->create([
                        'config' => $config,
                        'logger' => $this->logger,
                        'mockMode' => $account->getMockMode(),
                    ]);
                    // 50 limit
                    $orderList->setAmazonOrderIdFilter($orderId);
                    if ($params->getSyncMode() == ParamsInterface::COLUMN_SYNC_MODE_FETCH) {
                        $orderList->fetchOrders();
                        /** @var array $orders */
                        $orders = $orderList->getList();
                    } else {
                        // Loading saved data
                        $orders = $this->loadByData($orderId, $account->getId(), $config);
                    }

                    $log['options'] = $orderList->getOptions();
                } else {
                    // Single import by OrderId
                    /** @var \Amazon\Sdk\Api\Order $orderApi */
                    $orderApi = $this->orderApi->create([
                        'config' => $config,
                        'logger' => $this->logger,
                        'mockMode' => $account->getMockMode(),
                    ]);

                    $orderApi->setOrderId($orderId);
                    $orderApi->fetchOrder();
                    $orders[] = $orderApi;
                }
            } else {
                // Multiple import by set of filters
                /** @var \Amazon\Sdk\Api\Order\OrderList $orderList */
                $orderList = $this->orderListApi->create([
                    'config' => $config,
                    'logger' => $this->logger,
                    'mockMode' => $account->getMockMode(),
                ]);

                // Add CreatedAfter date Parameter: Required
                $orderList->setLimits($params->getType(), $params->getLowerDate(), $params->getUpperDate());

                // Adding BuyerEmail Parameter: Optional
                if (!empty($params->getBuyerEmail())) {
                    $orderList->setBuyerEmail($params->getBuyerEmail());
                }

                // Adding OrderStatus Parameter: Optional
                $orderList->setOrderStatusFilter($params->getStatus());

                $channel = $account->getChannel();
                $orderList->setFulfillmentChannelFilter($channel);

                $orderList->setMaxResultsPerPage((int)$params->getLimit());
                $orderList->setUseToken($params->getAllowPages());
                $orderList->fetchOrders();
                /** @var array $orders */
                $orders = $orderList->getList();
                $log['options'] = $orderList->getOptions();
            }
        } elseif ($params->getMode() ==
            \Ced\Amazon\Api\Data\Order\Import\ParamsInterface::IMPORT_MODE_REPORT) {
            /** @var \Amazon\Sdk\Api\Order\OrderList $orderList */
            $orderList = $this->orderListApi->create([
                'config' => $config,
                'logger' => $this->logger,
                'mockMode' => $account->getMockMode(),
            ]);
            $orderList->loadByReport($params->getPath());
            $orders = $orderList->getList();
        }

        $log['orders_count'] = is_array($orders) ? count($orders) : 0;
        $this->logger->notice(
            "Amazon order import for #" . $account->getId() . " " . $account->getName(),
            $log
        );

        return $orders;
    }

    private function loadByData($orderIdList = [], $accountId = null, $config = null)
    {
        $orders = [];

        /** @var \Ced\Amazon\Model\ResourceModel\Order\Collection $collection */
        $collection = $this->amazonOrderRepository
            ->getByAmazonOrderIds($orderIdList, $accountId, [\Ced\Amazon\Model\Source\Order\Status::SHIPPED]);

        /** @var \Ced\Amazon\Model\Order $item */
        foreach ($collection as $item) {
            /** @var \Amazon\Sdk\Api\Order $order */
            $order = $this->amazonApiOrderFactory->create([
                'id' => $item->getAmazonOrderId(),
                'data' => json_decode($item->getOrderData(), true),
                'config' => $config,
                'logger' => $this->logger
            ]);
            $order->setOrderItems(json_decode($item->getOrderItems(), true));
            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * Process Orders
     * @param array $orders
     * @param \Ced\Amazon\Api\Data\AccountInterface $account
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param boolean $create
     * @param int|null $cli
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function process($orders, $account, $store, $create = true, $cli = null)
    {
        $counter = 0;
        /** @var \Amazon\Sdk\Api\Order $order */
        foreach ($orders as $order) {
            if (!empty($cli) && $counter >= $cli) {
                // Limiting the import via CLI by providing the values
                break;
            }

            $salesChannel = $order->getSalesChannel();
            $groupId = null;
            $marketplaceId = $order->getMarketplaceId();
            if ($salesChannel == \Amazon\Sdk\Marketplace::SALES_CHANNEL_NON || empty($marketplaceId)) {
                // Skipping non-amazon|seller created fba orders as they miss customer data
                continue;
            }

            if ($account->getMultiStore()) {
                $storeId = $account->getStore($order->getMarketplaceId());
                /** @var \Magento\Store\Model\Store $store */
                $store = $this->storeManager->getStore($storeId);
                $groupId = $account->getCustomerGroup($order->getMarketplaceId());
            }

            $amazonOrderId = $order->getAmazonOrderId();

            try {
                /** @var \Ced\Amazon\Model\Order|null $marketplaceOrder */
                $marketplaceOrder = $this->amazonOrderRepository->getByPurchaseOrderId($amazonOrderId);
            } catch (\Exception $e) {
                // Create order in marketplace table, if not created.
                $marketplaceOrder = $this->amazonOrderRepository->create($account->getId(), null, $order, []);
            }

            // Create order in magento
            if (!empty($marketplaceOrder) && $create) {
                if (empty($marketplaceOrder->getData(\Ced\Amazon\Model\Order::COLUMN_MAGENTO_ORDER_ID)) &&
                    !in_array(
                        $order->getOrderStatus(),
                        [
                            \Ced\Amazon\Model\Source\Order\Status::CANCELLED,
                            \Ced\Amazon\Model\Source\Order\Status::PENDING
                        ]
                    )) {
                    // If increment id is already available, then set and continue.
                    if ($this->config->isOrderIdSameAsPoId() && $this->exists($order, $marketplaceOrder)) {
                        continue;
                    }

                    // If guest order is enabled, do not create customer.
                    $guest = $this->config->getGuestCustomer();
                    $customer = null;
                    if (!$guest) {
                        $customer = $this->customer->get($order, $store, $groupId);
                    }

                    if (!empty($customer) || $guest) {
                        // Creating the order
                        $this->quote->setMedium($this->getMedium());
                        $this->quote->setStore($store);
                        $this->quote->setAccount($account);
                        $this->quote->setCustomer($customer);
                        $imported = $this->quote->create($order);

                        if ($imported) {
                            $this->result->addId($order->getAmazonOrderId());
                        }
                        // Adding the counter
                        $this->imported += (bool)$imported ? 1 : 0;
                    } else {
                        // Adding customer save error in order failure reasons.
                        $marketplaceOrder->setData(
                            \Ced\Amazon\Model\Order::COLUMN_FAILURE_REASON,
                            $this->serializer->serialize([
                                Reason::ERROR_CUSTOMER_CREATE_FAILURE_CODE => Reason::ERROR_MESSAGE_CUSTOMER_CREATE_FAILURE_MESSAGE
                            ])
                        );
                        $this->amazonOrderRepository->save($marketplaceOrder);
                        continue;
                    }
                } else {
                    // Updating order status
                    $marketplaceOrder->setData(
                        \Ced\Amazon\Model\Order::COLUMN_STATUS,
                        $order->getOrderStatus()
                    );
                    $marketplaceOrder->setData(
                        \Ced\Amazon\Model\Order::COLUMN_ORDER_DATA,
                        $this->serializer->serialize($order->getData())
                    );
                    $this->amazonOrderRepository->save($marketplaceOrder);
                    continue;
                }

                $counter++;
            }
        }
    }

    private function processTaxRegions()
    {
        $regions = $this->regionFactory->create()
            ->getCollection()
            ->addFieldToSelect(['country_id', 'code'])
            ->addFieldToFilter('country_id', ['eq' => 'US'])
            ->addFieldToFilter('code', ['in' => ['FL', 'NC', 'GA']]);

        /** @var \Magento\Directory\Model\Region $region */
        foreach ($regions as $region) {
            $this->regions[$region->getData('code')] = $region->getId();
        }

        $this->quote->setRegions($this->regions);
    }

    /**
     * Check if order increment id is already created in Magento
     * @param \Amazon\Sdk\Api\Order $apiOrder
     * @param \Ced\Amazon\Model\Order $marketplaceOrder
     * @return bool
     */
    private function exists($apiOrder, $marketplaceOrder)
    {
        $status = false;
        try {
            /** @var \Magento\Sales\Api\Data\OrderInterface|null $available */
            $available = $this->findByIncrementId($this->generateIncrementId("", $apiOrder));
            if (!empty($available)) {
                $marketplaceOrder->setData(\Ced\Amazon\Model\Order::COLUMN_STATUS, $apiOrder->getOrderStatus());
                $marketplaceOrder->setData(\Ced\Amazon\Model\Order::COLUMN_FAILURE_REASON, '[]');
                $marketplaceOrder->setData(
                    \Ced\Amazon\Model\Order::COLUMN_MAGENTO_ORDER_ID,
                    $available->getEntityId()
                );
                $marketplaceOrder->setData(
                    \Ced\Amazon\Model\Order::COLUMN_MAGENTO_INCREMENT_ID,
                    $available->getIncrementId()
                );
                $status = (bool)$this->amazonOrderRepository->save($marketplaceOrder);
            }
        } catch (\Exception $e) {
            $status = false;
        }

        return $status;
    }
}
