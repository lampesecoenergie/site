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

namespace Ced\Amazon\Helper;

use Ced\Amazon\Api\Service\ProductServiceInterface;
use Ced\Amazon\Registry\Order as AmazonOrderRegistry;
use Ced\Integrator\Api\GeocodeRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Class Order
 * @package Ced\Amazon\Helper
 */
class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
    const DEFAULT_EMAIL = "customer@amazon.com";

    const ERROR_OUT_OF_STOCK_CODE = "E101";
    const ERROR_OUT_OF_STOCK = "'%s' SKU out of stock";

    const ERROR_NOT_ENABLED_CODE = "E102";
    const ERROR_NOT_ENABLED = "'%s' SKU not enabled on store '%s'";

    const ERROR_DOES_NOT_EXISTS_CODE = "E103";
    const ERROR_DOES_NOT_EXISTS = "'%s' SKU not exists on store '%s'";

    const ERROR_ITEM_DATA_NOT_AVAILABLE_CODE = "E104";
    const ERROR_ITEM_DATA_NOT_AVAILABLE = "'%s' SKU not available in order items or order item is cancelled. [qty: %s]";

    const ERROR_CUSTOMER_CREATE_FAILURE_CODE = 'E105';
    const ERROR_CUSTOMER_CREATE_FAILURE_MESSAGE =
        'Unable to assign the customer. Customer is not available or cannot be created.';

    const ERROR_ORDER_IMPORT_EXCEPTION_CODE = 'E500';
    const ERROR_ORDER_IMPORT_EXCEPTION = 'Exception occurred during order import. Kindly contact support.';

    /**
     * @var \Magento\Framework\objectManagerInterface
     */
    public $objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var CustomerInterfaceFactory
     */
    public $customerFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    public $customerRepository;

    /**
     * @var ProductServiceInterface
     */
    public $productService;

    /** @var GeocodeRepositoryInterface */
    public $geocodeRepository;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $product;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
    public $productCollectionFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $json;

    /**
     * @var \Magento\Sales\Model\Service\OrderService
     */
    public $orderService;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory
     */
    public $creditmemoLoaderFactory;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    public $cartManagementInterface;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    public $cartRepositoryInterface;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    public $stockRegistry;

    /**
     * @var \Ced\Amazon\Model\OrderFactory
     */
    public $orderFactory;

    /**
     * @var \Ced\Amazon\Repository\Account
     */
    public $accountRepository;

    /** @var \Magento\Framework\Notification\NotifierInterface */
    public $notifier;

    /**
     * @var
     */
    public $messageManager;

    /**
     * @var \Ced\Amazon\Api\FeedRepositoryInterface
     */
    public $feed;

    /** @var AmazonOrderRegistry */
    public $amazonOrderRegistry;

    /**
     * @var $config
     */
    public $config;

    /**
     * @var Logger
     */
    public $logger;

    /**
     * @var \Amazon\Sdk\Api\Order\OrderList
     */
    public $orderListApi;

    /** @var \Amazon\Sdk\Api\OrderFactory */
    public $orderApi;

    /** @var \Ced\Amazon\Model\MailFactory */
    public $mailFactory;

    /** @var \Magento\Framework\DataObjectFactory */
    public $dataFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /** @var \Magento\Directory\Model\RegionFactory */
    public $regionFactory;

    /** @var \Magento\Customer\Model\AddressFactory */
    public $addressFactory;

    /** @var Product\Inventory */
    public $inventory;

    /** @var \Magento\Quote\Model\Quote\Address\RateFactory */
    public $rateFactory;

    /** @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory */
    public $orderCollectionFactory;

    /** @var \Magento\Quote\Model\Cart\CurrencyFactory */
    public $quoteCurrencyFactory;

    /** @var \Magento\Directory\Model\CurrencyFactory */
    public $directoryCurrencyFactory;

    /**
     * @var \Ced\Amazon\Model\Order\ItemFactory
     */
    public $orderItemFactory;

    /** @var int */
    public $imported = 0;

    /** @var array */
    public $USTaxedRegions = [];

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\DataObjectFactory $dataFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomerInterfaceFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        ProductServiceInterface $productService,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Framework\Registry $registry,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Quote\Model\Quote\Address\RateFactory $rateFactory,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Framework\Notification\NotifierInterface $notifier,
        \Magento\Framework\Message\ManagerInterface $manager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Ced\Amazon\Repository\Account $accountRepository,
        \Ced\Amazon\Model\OrderFactory $orderFactory,
        \Ced\Amazon\Model\MailFactory $mailFactory,
        \Ced\Amazon\Api\FeedRepositoryInterface $feed,
        \Ced\Amazon\Helper\Config $config,
        \Ced\Amazon\Helper\Logger $logger,
        \Ced\Amazon\Helper\Product\Inventory $inventory,
        AmazonOrderRegistry $amazonOrderRegistry,
        \Amazon\Sdk\Api\OrderFactory $sdkOrder,
        \Amazon\Sdk\Api\Order\OrderListFactory $sdkListOrder,
        \Magento\Quote\Model\Cart\CurrencyFactory $quoteCurrencyFactory,
        \Magento\Directory\Model\CurrencyFactory $directoryCurrencyFactory,
        GeocodeRepositoryInterface $geocodeRepository,
        \Ced\Amazon\Model\Order\ItemFactory $orderItemFactory
    ) {
        parent::__construct($context);
        $this->regionFactory = $regionFactory;
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->productService = $productService;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->product = $product;
        $this->json = $json;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderService = $orderService;
        $this->rateFactory = $rateFactory;
        $this->creditmemoLoaderFactory = $creditmemoLoaderFactory;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->notifier = $notifier;
        $this->messageManager = $manager; //remove
        $this->stockRegistry = $stockRegistry;
        $this->registry = $registry;
        $this->addressFactory = $addressFactory;

        $this->inventory = $inventory;
        $this->orderFactory = $orderFactory;
        $this->mailFactory = $mailFactory;
        $this->dataFactory = $dataFactory;
        $this->accountRepository = $accountRepository;
        $this->amazonOrderRegistry = $amazonOrderRegistry;
        $this->geocodeRepository = $geocodeRepository;

        $this->feed = $feed;
        $this->logger = $logger;
        $this->config = $config;
        $this->orderListApi = $sdkListOrder;
        $this->orderApi = $sdkOrder;
        $this->quoteCurrencyFactory = $quoteCurrencyFactory;
        $this->directoryCurrencyFactory = $directoryCurrencyFactory;

        $this->orderItemFactory = $orderItemFactory;
    }

    /**
     * Import Orders from Amazon
     * @param array $accountIds
     * @param null $orderId
     * @param null $buyerEmail
     * @param array $status
     * @param null $lower
     * @param int $limit
     * @param boolean $page
     * @param string $type , Created or Modified
     * @param null $upper
     * @return bool|int
     * @throws \Exception
     */
    public function import(
        $accountIds = [],
        $orderId = null,
        $buyerEmail = null,
        array $status = [
            \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_UNSHIPPED,
            \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_PARTIALLY_SHIPPED
        ],
        $lower = null,
        $limit = 100,
        $page = true,
        $type = "Created",
        $upper = null
    ) {
        $this->init();

        /** @var \Ced\Amazon\Api\Data\AccountSearchResultsInterface $accounts */
        $accounts = $this->accountRepository->getAvailableList($accountIds);

        \Magento\Framework\Profiler::start('ced-amazon-profile-order-import');

        /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
        foreach ($accounts->getItems() as $account) {
            /** @var int $storeId */
            $storeId = $account->getStore();
            /** @var \Magento\Store\Api\Data\StoreInterface $store */
            $store = $this->storeManager->getStore($storeId);

            /** @var \Amazon\Sdk\Api\Config $config */
            $config = $account->getConfig();

            /** @var array $orders */
            $orders = [];

            $log = [
                "account_name" => $account->getName(),
                "type" => $type,
            ];
            // Single import by OrderId
            if (isset($orderId) && !empty($orderId)) {
                /** @var \Amazon\Sdk\Api\Order $orderApi */
                $orderApi = $this->orderApi->create([
                    'config' => $config,
                    'logger' => $this->logger,
                    'mockMode' => $account->getMockMode(),
                ]);

                $orderApi->setOrderId($orderId);
                $orderApi->fetchOrder();
                $orders[] = $orderApi;
            } else {
                // Multiple import by set of filters
                /** @var \Amazon\Sdk\Api\Order\OrderList $orderList */
                $orderList = $this->orderListApi->create([
                    'config' => $config,
                    'logger' => $this->logger,
                    'mockMode' => $account->getMockMode(),
                ]);

                // Add CreatedAfter date Parameter: Required
                if (!isset($lower) || empty($lower)) {
                    $lower = date('Y-m-d H:i:s O', strtotime($this->config->getImportTime()));
                }

                $orderList->setLimits($type, $lower, $upper);

                // Adding BuyerEmail Parameter: Optional
                if (isset($buyerEmail) && !empty($buyerEmail)) {
                    $orderList->setBuyerEmail($buyerEmail);
                }

                // Adding OrderStatus Parameter: Optional
                if (!isset($status) || empty($status) || in_array(
                        $status,
                        [
                            \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_UNSHIPPED,
                            \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_PARTIALLY_SHIPPED
                        ]
                    )
                ) {
                    $status = [
                        \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_UNSHIPPED,
                        \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_PARTIALLY_SHIPPED
                    ];
                }

                $orderList->setOrderStatusFilter($status);

                $channel = $account->getChannel();
                $orderList->setFulfillmentChannelFilter($channel);

                $orderList->setMaxResultsPerPage((int)$limit);
                $orderList->setUseToken($page);
                $orderList->fetchOrders();
                /** @var array $orders */
                $orders = $orderList->getList();
                $log['options'] = $orderList->getOptions();
            }

            $log['orders_count'] = count($orders);
            $this->logger->notice("Amazon order import for #" . $account->getId() . " " . $account->getName(), $log);

            /** @var \Amazon\Sdk\Api\Order $order */
            foreach ($orders as $order) {
                $this->amazonOrderRegistry->clear();
                $customerGroupId = false;
                if ($account->getMultiStore()) {
                    $storeId = $account->getStore($order->getMarketplaceId());
                    /** @var \Magento\Store\Model\Store $store */
                    $store = $this->storeManager->getStore($storeId);
                    $customerGroupId = $account->getCustomerGroup($order->getMarketplaceId());
                }

                $amazonOrderId = $order->getAmazonOrderId();

                /** @var \Ced\Amazon\Model\Order|null $mporder */
                $mporder = $this->orderFactory->create()->getByPurchaseOrderId($amazonOrderId);
                if (empty($mporder)) {
                    // Create order in marketplace table, if not created.
                    $mporder = $this->create($account->getId(), null, $order, []);
                }

                // Create order in magento
                if (!empty($mporder)) {
                    if (empty($mporder->getData(\Ced\Amazon\Model\Order::COLUMN_MAGENTO_ORDER_ID)) &&
                        !in_array(
                            $order->getOrderStatus(),
                            [
                                \Ced\Amazon\Model\Source\Order\Status::CANCELLED,
                                \Ced\Amazon\Model\Source\Order\Status::PENDING
                            ]
                        )) {
                        if ($this->config->isOrderIdSameAsPoId()) {
                            /** @var \Magento\Sales\Api\Data\OrderInterface|null $available */
                            $available = $this->checkIfExists($this->generateIncrementId("", $order));
                            if (!empty($available)) {
                                $mporder->setData(\Ced\Amazon\Model\Order::COLUMN_STATUS, $order->getOrderStatus());
                                $mporder->setData(\Ced\Amazon\Model\Order::COLUMN_FAILURE_REASON, '[]');
                                $mporder->setData(
                                    \Ced\Amazon\Model\Order::COLUMN_MAGENTO_ORDER_ID,
                                    $available->getEntityId()
                                );
                                $mporder->setData(
                                    \Ced\Amazon\Model\Order::COLUMN_MAGENTO_INCREMENT_ID,
                                    $available->getIncrementId()
                                );
                                $mporder->save();
                                continue;
                            }
                        }

                        $guest = $this->config->getGuestCustomer();
                        $customer = null;
                        if (!$guest) {
                            $customer = $this->getCustomer($order, $store, $customerGroupId);
                        }

                        if (!empty($customer) || $guest) {
                            $this->amazonOrderRegistry->setOrder($order);
                            $this->quote($store, $customer, $order, $mporder->getId(), $account);
                        } else {
                            // Adding customer save error in order failure reasons.
                            $mporder->setData(
                                \Ced\Amazon\Model\Order::COLUMN_FAILURE_REASON,
                                $this->json->jsonEncode(
                                    [
                                        self::ERROR_CUSTOMER_CREATE_FAILURE_CODE => self::ERROR_CUSTOMER_CREATE_FAILURE_MESSAGE
                                    ]
                                )
                            );
                            $mporder->save();
                            continue;
                        }
                    } else {
                        // Updating order status
                        $mporder->setData(\Ced\Amazon\Model\Order::COLUMN_STATUS, $order->getOrderStatus());
                        $mporder->setData(
                            \Ced\Amazon\Model\Order::COLUMN_ORDER_DATA,
                            $this->json->jsonEncode($order->getData())
                        );
                        $mporder->save();
                        continue;
                    }
                }
            }
        }

        \Magento\Framework\Profiler::stop('ced-amazon-profile-order-import');

        if (isset($this->imported) && $this->imported > 0) {
            $this->notify(
                "New Amazon Orders",
                "Congratulation! You have received {$this->imported} new orders form Amazon"
            );
            return $this->imported;
        }

        return false;
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
            $this->USTaxedRegions[$region->getData('code')] = $region->getId();
        }
    }

    /**
     * TODO: Move to order service
     * Create an Order in Amazon table
     * @param $accountId
     * @param \Magento\Sales\Model\Order|null $order
     * @param \Amazon\Sdk\Api\Order $data
     * @param array $items
     * @return \Ced\Amazon\Model\Order|null
     * @throws \Exception
     */
    public function create($accountId, $order = null, \Amazon\Sdk\Api\Order $data = null, $items = [])
    {
        try {
            // after save order
            $orderPlace = substr($data->getPurchaseDate(), 0, 10);
            $status = $data->getOrderStatus();
            $orderData = [
                \Ced\Amazon\Model\Order::COLUMN_ACCOUNT_ID => $accountId,
                \Ced\Amazon\Model\Order::COLUMN_PO_ID => $data->getAmazonOrderId(),
                \Ced\Amazon\Model\Order::COLUMN_MARKETPLACE_ID => $data->getMarketplaceId(),
                \Ced\Amazon\Model\Order::COLUMN_PO_DATE => $orderPlace,
                \Ced\Amazon\Model\Order::COLUMN_STATUS => ($status == \Amazon\Sdk\Api\Order\Core::ORDER_STATUS_UNSHIPPED) ?
                    \Ced\Amazon\Model\Source\Order\Status::NOT_IMPORTED : $status,
                \Ced\Amazon\Model\Order::COLUMN_ORDER_DATA => $this->json->jsonEncode($data->getData()),
            ];

            if (isset($order) && !empty($order->getId())) {
                $orderData[\Ced\Amazon\Model\Order::COLUMN_MAGENTO_ORDER_ID] = $order->getId();
                $orderData[\Ced\Amazon\Model\Order::COLUMN_MAGENTO_INCREMENT_ID] = $order->getIncrementId();
                if ($orderData[\Ced\Amazon\Model\Order::COLUMN_STATUS] ==
                    \Ced\Amazon\Model\Source\Order\Status::NOT_IMPORTED) {
                    $orderData[\Ced\Amazon\Model\Order::COLUMN_STATUS] =
                        \Ced\Amazon\Model\Source\Order\Status::IMPORTED;
                }
            }

            if (!empty($items)) {
                $orderData[\Ced\Amazon\Model\Order::COLUMN_ORDER_ITEMS] = $this->json->jsonEncode($items);
            }

            /** @var \Ced\Amazon\Model\Order $mporder */
            $mporder = $this->orderFactory->create()->addData($orderData);
            return $mporder->save();
        } catch (\Exception $e) {
            $this->logger->addCritical(
                'Order create failed in marketplace table.',
                [
                    'marketplace' => 'Amazon',
                    'account_id' => $accountId,
                    'api_data' => $data,
                ]
            );
            return null;
        }
    }

    /**
     * TODO: Move to customer service
     * Get or Create Customer
     * @param \Amazon\Sdk\Api\Order $order
     * @param \Magento\Store\Model\Store $store
     * @return null|\Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer(\Amazon\Sdk\Api\Order $order, $store, $customerGroup)
    {
        $customer = null;

        try {
            $email = $this->config->getDefaultCustomer();
            if (!empty($email)) {
                /**
                 * case 1: Use default customer.
                 */
                try {
                    /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
                    $customer = $this->customerRepository->get($email, $store->getWebsiteId());
                } catch (\Exception $e) {
                    throw new LocalizedException(
                        __("Default Customer does not exists. Customer Id: #%s.", $email)
                    );
                }
            } else {
                /** Case 2: Use Customer from Order.*/
                $email = $this->getEmail($order);
                try {
                    /** Case 2.1 Get Customer if already exists. */
                    /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
                    $customer = $this->customerRepository->get($email, $store->getWebsiteId());
                } catch (\Exception $e) {
                    // Case 2.1 : Create customer if does not exists.
                    if(empty($order->getBuyerName())) {
                        $name = ['Amazon Customer', 'FBA'];
                    } else {
                        $name = explode(' ', $order->getBuyerName(), 2);
                    }
                    /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
                    $customer = $this->customerFactory->create();
                    $customer->setStoreId($store->getStoreId());
                    $customer->setWebsiteId($store->getWebsiteId());
                    $customer->setEmail($email);
                    $customer->setFirstname(isset($name[0]) ? $name[0] : '.');
                    $customer->setLastname(isset($name[1]) ? $name[1] : '.');
                    if ($customerGroup!== false) {
                        $customer->setGroupId($customerGroup);
                    }
                    $customer = $this->customerRepository->save($customer);
                }
            }
        } catch (\Exception $e) {
            $customer = null;
            $this->logger->log(
                'ERROR',
                'Customer create failed. Order Id: #' . $order->getAmazonOrderId(),
                [
                    'message' => $e->getMessage(),
                    'order_id' => $order->getAmazonOrderId()
                ]
            );
        }

        return $customer;
    }

    /**
     * Get Email for Order
     * @param \Amazon\Sdk\Api\Order $order
     * @return string
     */
    private function getEmail(\Amazon\Sdk\Api\Order $order)
    {
        $email = $order->getBuyerEmail();
        if (isset($email) && !empty($email)) {
            return $email;
        } else {
            // BuyerEmail is not returned for Fulfillment by Amazon gift orders.
            return $order->getAmazonOrderId() . "@amazon.com";
        }
    }

    /**
     * Process Region Id
     * @param string $name , Region name
     * @param string $city , City name
     * @param string $countryId , Country id example: US
     * @param string $pin , PostalCode
     * @return null|int
     */
    private function region($name, $city, $countryId, $pin = '')
    {
        $create = $this->config->createRegion();
        $geocode = $this->config->useGeocode();
        $default = $this->config->useDash();

        $regionId = null;

        try {
            /** @var \Magento\Directory\Model\Region $region */
            $region = $this->regionFactory->create();
            $region->loadByName($name, $countryId);
            $regionId = $region->getRegionId();

            if (empty($regionId)) {
                // Match by the short-code
                $regionId = $region->loadByCode($name, $countryId)->getRegionId();
            }

            /** @var \Magento\Directory\Helper\Data $regionHelper */
            $regionHelper = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Directory\Helper\Data::class);
            if (empty($regionId) && $regionHelper->isRegionRequired($countryId)) {
                // 1. If region is required and not present in Magento directory and $create is allowed,
                // creating a new and, setting it on order address.
                if (!empty($countryId) && !empty($name) && $create) {
                    $code = $name;
                    if ($geocode) {
                        // Get the State Name
                        /** @var \Ced\Integrator\Api\Data\Geocode\StateInterface $state */
                        $state = $this->geocodeRepository->getStateByPincodeAndCity($pin, $city);
                        if (!empty($state->getShortName())) {
                            $name = $state->getLongName();
                            $code = $state->getShortName();
                            // Match by the short-code
                            $regionId = $region->loadByCode($code, $countryId)->getRegionId();
                        }
                    }

                    if (!empty($regionId)) {
                        $regionId = $region->setData('country_id', $countryId)
                            ->setData('code', $code)
                            ->setData('default_name', $name)
                            ->setData('name', $name)
                            ->save()
                            ->getRegionId();
                    }
                } else {
                    // 2. If region is not present use city
                    $region->loadByName($city, $countryId);
                    $regionId = $region->getRegionId();

                    if ($geocode) {
                        // 3. Get the State By City and Create, if Geocode is enabled
                        /** @var \Ced\Integrator\Api\Data\Geocode\StateInterface $state */
                        $state = $this->geocodeRepository->getStateByPincodeAndCity($pin, $city);

                        if (!empty($state->getShortName())) {
                            $name = $state->getLongName();
                            $code = $state->getShortName();
                            // Match by the short-code
                            $regionId = $region->loadByCode($code, $countryId)->getRegionId();
                            if (empty($regionId)) {
                                $regionId = $region->setData('country_id', $countryId)
                                    ->setData('code', $code)
                                    ->setData('default_name', $name)
                                    ->setData('name', $name)
                                    ->save()
                                    ->getRegionId();
                            }
                        }
                    }

                    // 4. If city as region is not available and $default is allowed, use default value '-'
                    if (empty($regionId) && $default) {
                        $regionId = $region->loadByName('-', $countryId)->getRegionId();

                        // 5. If default value not present create with $countryId as country
                        if (empty($regionId)) {
                            $regionId = $region->setData('country_id', $countryId)
                                ->setData('code', '-')
                                ->setData('default_name', '-')
                                ->setData('name', '-')
                                ->save()
                                ->getRegionId();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Silence
        }

        return $regionId;
    }

    /**
     * Set the total shipping in registry
     * @param float $amount
     */
    private function registerShippingAmount($amount)
    {
        $this->registry->unregister(\Ced\Amazon\Model\Carrier\Shipbyamazon::REGISTRY_INDEX_SHIPPING_TOTAL);

        if ($this->registry->registry(\Ced\Amazon\Model\Carrier\Shipbyamazon::REGISTRY_INDEX_SHIPPING_TOTAL) != null) {
            $this->registry->unregister(\Ced\Amazon\Model\Carrier\Shipbyamazon::REGISTRY_INDEX_SHIPPING_TOTAL);
        }

        $this->registry->register(
            \Ced\Amazon\Model\Carrier\Shipbyamazon::REGISTRY_INDEX_SHIPPING_TOTAL,
            $amount
        );
    }

    /**
     * Set the shipping method in registry
     * @param string $method
     */
    private function registerShippingMethod($method)
    {
        $this->registry->unregister(\Ced\Amazon\Model\Carrier\Shipbyamazon::REGISTRY_INDEX_SHIPPING_METHOD);

        if ($this->registry->registry(\Ced\Amazon\Model\Carrier\Shipbyamazon::REGISTRY_INDEX_SHIPPING_METHOD) != null) {
            $this->registry->unregister(\Ced\Amazon\Model\Carrier\Shipbyamazon::REGISTRY_INDEX_SHIPPING_METHOD);
        }

        $this->registry->register(
            \Ced\Amazon\Model\Carrier\Shipbyamazon::REGISTRY_INDEX_SHIPPING_METHOD,
            $method
        );
    }

    /**
     * Generate Quote
     * @param \Magento\Store\Model\Store $store
     * @param \Magento\Customer\Api\Data\CustomerInterface|null $customer
     * @param \Amazon\Sdk\Api\Order|null $order
     * @param int $mporderId
     * @param \Ced\Amazon\Model\Account $account
     * @return int
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function quote(
        $store,
        $customer,
        \Amazon\Sdk\Api\Order $order = null,
        $mporderId = null,
        $account = null
    ) {
        $amazonOrderId = '';
        $reason = [];
        $customStock = [];
        try {
            /** @var boolean $importTax , For US tax import only for FL, GA, NC states */
            $importTax = $this->config->getUSTaxImport();
            $importShippingTax = $this->config->getShippingTaxImport();

            /** @var string $amazonOrderId */
            $amazonOrderId = $order->getAmazonOrderId();

            /** @var \Amazon\Sdk\Api\Order\ItemList $items */
            $items = $order->fetchItems(true);
            $this->amazonOrderRegistry->setItems($items);

            $shippingTotal = 0.00;
            $shippingTax = 0.00;
            $shippingDiscount = 0.00;

            $discountAmount = 0.00;
            $discount = [];

            $tax = [];
            $orderItemsCollection = [];
            $totalTax = 0.00;

            /** @var \Magento\Quote\Model\Cart\Currency $quoteCurrency */
            $quoteCurrency = $this->quoteCurrencyFactory->create()
                ->setGlobalCurrencyCode($order->getCurrencyCode())
                ->setBaseCurrencyCode($order->getCurrencyCode())
                ->setStoreCurrencyCode($order->getCurrencyCode())
                ->setQuoteCurrencyCode($order->getCurrencyCode())
                ->setStoreToBaseRate(1.00)
                ->setStoreToQuoteRate(1.00)
                ->setBaseToGlobalRate(1.00)
                ->setBaseToQuoteRate(1.00);
            /** @var \Magento\Directory\Model\Currency $directoryCurrency */
            $directoryCurrency = $this->directoryCurrencyFactory->create()
                ->setCurrencyCode($order->getCurrencyCode())
                ->setGlobalCurrencyCode($order->getCurrencyCode())
                ->setBaseCurrencyCode($order->getCurrencyCode())
                ->setStoreCurrencyCode($order->getCurrencyCode())
                ->setQuoteCurrencyCode($order->getCurrencyCode())
                ->setStoreToBaseRate(1.00)
                ->setStoreToQuoteRate(1.00)
                ->setBaseToGlobalRate(1.00)
                ->setBaseToQuoteRate(1.00);

            // Overriding base currency
            $store->setBaseCurrency($directoryCurrency);

            $cartId = $this->cartManagementInterface->createEmptyCart();
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->cartRepositoryInterface->get($cartId);
            $quote->setStore($store);

            // forcefully changing the currency for quote
            $quote->setForcedCurrency($directoryCurrency);
            $quote->setBaseCurrencyCode($order->getCurrencyCode());
            $quote->setGlobalCurrencyCode($order->getCurrencyCode());
            $quote->setStoreCurrencyCode($order->getCurrencyCode());
            $quote->setQuoteCurrencyCode($order->getCurrencyCode());
            $quote->setCurrency($quoteCurrency);

            /** @var array $address */
            $address = $order->getShippingAddress();
            /** @var array $name */
            $name = isset($address['Name']) && !empty($address['Name']) ? explode(' ', $address['Name'], 2) :
                explode(' ', (string)$order->getBuyerName(), 2);
            if ($this->config->getGuestCustomer()) {
                $quote->setCustomerFirstname($this->getValue(0, $name, 'N/A'));
                $quote->setCustomerLastname($this->getValue(1, $name, 'N/A'));
                $quote->setCustomerEmail($this->getEmail($order));
                $quote->setCustomerIsGuest(true);
            } else {
                $quote->assignCustomer($customer);
            }

            $quote->setCustomerNoteNotify(false);
            $itemAccepted = 0;
            $itemOrdered = 0;
            $bundleItems = 0;
            $productIds = [];

            // Flags for inclusive taxes, Taxes are inclusive in Europe but exclusive in US
            $inclusiveTax = (\Amazon\Sdk\Marketplace::getRegionByMarketplaceId($order->getMarketplaceId()) ==
                \Amazon\Sdk\Marketplace::REGION_EUROPE) ? true : false;
            $inclusiveShippingTax = (\Amazon\Sdk\Marketplace::getRegionByMarketplaceId($order->getMarketplaceId()) ==
                \Amazon\Sdk\Marketplace::REGION_EUROPE) ? true : false;

            // getting mapped inventory attribute for this account.
            $mappedAttribute = false;
            $mappedInventoryAttribute = $this->config->getInventoryAttribute();
            if (isset($mappedInventoryAttribute[$account->getId()])) {
                $mappedAttribute = $mappedInventoryAttribute[$account->getId()];
            }

            foreach ($items as $index => $item) {
                $item = $this->checkIfCancelled($item, $items->getItems());
                $sku = $items->getSellerSKU($index);
                $qty = isset($item['QuantityOrdered']) ? $item['QuantityOrdered'] : 0;
                if (isset($item['SellerSKU'], $item['QuantityOrdered']) && $item['QuantityOrdered'] > 0) {
                    $itemOrdered++;
                    $qty = $item['QuantityOrdered'];
                    /** @var \Magento\Catalog\Model\Product $product */
                    $product = $this->productService->find($item['SellerSKU']);

                    // creating unavailable product during order import
                    if (($this->config->createUnavailableProduct()) && (!isset($product) || empty($product))) {
                        $product = $this->product->create();
                        $product->setName($items->getTitle($index));
                        $product->setTypeId('simple');
                        $product->setAttributeSetId($product->getDefaultAttributeSetId());
                        $product->setSku($sku);
                        $product->setWebsiteIds([1]);
                        $product->setVisibility(4);
                        $product->setUrlKey($sku);
                        $product->setPrice(($items->getItemPrice($index)));
                        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                        $product->setData('is_amazon', 1);
                        $product->setData('is_salable', 1);
                        $product->setStockData(
                            [
                                'manage_stock' => 1,
                                'is_in_stock' => 1,
                                'qty' => $qty
                            ]
                        );
                        $product =  $this->productService->productRepository->save($product);
                    }

                    if (isset($product) && !empty($product)) {
                        $backorder = false;
                        $quantity[$product->getId()] = $qty;
                        if ($product->getStatus() == '1' || $this->config->createBackorder()) {
                            $productIds[$product->getId()] = $product->getId();
                            $sku = $product->getSku();

                            if ($mappedAttribute) {
                                /* Get stock item from mapped inventory attribute */
                                $stockInAttribute = (int)$product->getData($mappedAttribute);
                                $stockStatus = ($stockInAttribute > 0) ?
                                    (($stockInAttribute >= $qty) ? true : false) : false;
                            } else {
                                /* Get stock item */
                                $stock = $this->stockRegistry
                                    ->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
                                $stockStatus = ($stock->getQty() > 0) ? ($stock->getIsInStock() == '1' ?
                                    ($stock->getQty() >= $qty ? true : false)
                                    : false) : false;
                            }
                            $stockStatus = $this->config->createBackorder() ? true : $stockStatus;
                            if ($stockStatus) {
                                if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                                    $discount[$product->getId()] = abs((float)$items->getPromotionDiscount($index, true));
                                    $discountAmount += abs((float)$discount[$product->getId()]);
                                    $shippingDiscount += abs((float)$items->getShippingDiscount($index, true));
                                } else {
                                    $discount[$sku] = (float)$items->getPromotionDiscount($index, true);
                                    $discountAmount += $discount[$sku];
                                    $shippingDiscount += abs((float)$items->getShippingDiscount($index, true));
                                }

                                if ($importShippingTax) {
                                    $shippingTax += (float)$items->getShippingTax($index, true);
                                }

                                $shippingAmount = (float)$items->getShippingPrice($index, true);
                                if ($inclusiveShippingTax) {
                                    $shippingAmount = $shippingAmount - $shippingTax;
                                }

                                $shippingTotal += $shippingAmount;

                                $price = $items->getItemPrice($index);

                                if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                                    $tax[$product->getId()] = $items->getItemTax($index, true);
                                } else {
                                    $tax[$sku] = $items->getItemTax($index, true);
                                }
                                if ($inclusiveTax) {
                                    $taxPerItem = $items->getTaxPerItem($index, true);
                                    $price = $price - $taxPerItem;
                                }

                                /*$product->setPrice($price - $discount[$sku])
                                    ->setOriginalCustomPrice($price - $discount[$sku]);*/

                                $rowTotal = (float)$price * (float)$qty;
                                $baseprice = $qty * $price;
                                $product
                                    ->setTaxClassId($this->getTaxClassId())
                                    ->setPrice($price)
                                    ->setSpecialPrice($price)
                                    ->setTierPrice([])
                                    ->setBasePrice($baseprice)
                                    ->setOriginalCustomPrice($price)
                                    ->setRowTotal($rowTotal)
                                    ->setBaseRowTotal($rowTotal)
                                    ->setFinalPrice($rowTotal);

                                $quoteItemPrice[$product->getId()]['price'] = $price;
                                $quoteItemPrice[$product->getId()]['base_price'] = $baseprice;
                                $quoteItemPrice[$product->getId()]['row_total'] = $rowTotal;

                                /** @var \Magento\Framework\DataObject $request */
                                $request = $this->dataFactory->create();
                                $request->setData('qty', (int)$qty);

                                if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                                    $bundleOptions = [];
                                    $bundleOptionsQty = [];
                                    /**
                                     * @var \Magento\Bundle\Model\Option $option
                                     */
                                    foreach ($product->getExtensionAttributes()->getBundleProductOptions() as $option) {
                                        if ($option->getRequired()) {
                                            foreach ($option->getProductLinks() as $selection) {
                                                /**
                                                 * @var \Magento\Bundle\Api\Data\LinkInterface $selection
                                                 */
                                                if ($selection->getIsDefault()) {
                                                    $bundleOptions[$option->getId()][] = $selection->getId();
                                                    if ($bundleItems === 0) {
                                                        $bundleOptionsQty[$option->getId()][] = $qty;
                                                    } else {
                                                        $bundleOptionsQty[$option->getId()][] = 0;
                                                    }
                                                    $bundleItems++;
                                                }
                                            }
                                        }
                                    }
                                    $request->addData(
                                        [
                                            'qty' => (int)$qty,
                                            'bundle_option' => $bundleOptions,
                                            'bundle_option_qty' => $bundleOptionsQty
                                        ]
                                    );
                                    $product->setSkipCheckRequiredOption(true);
                                }

                                $product->setIsSuperMode(true);
                                $product->unsSkipCheckRequiredOption();
                                $product->setSkipSaleableCheck(true);
                                $product->setData('salable', true);
                                $quote->setIsSuperMode(true);
                                $quote->setIgnoreOldQty(true);
                                $quote->setData("amazon_order_id", $order->getAmazonOrderId());
                                $product->setHasOptions(false);
//                                $product->setTypeInstance(null);
//                                $product->setTypeId('simple');
                                $addedItem = $quote->addProduct($product, $request);
                                if ($addedItem instanceof \Magento\Quote\Model\Quote\Item) {
                                    $addedItem->setData("amazon_order_id", $order->getAmazonOrderId());
                                    $addedItem->setData("amazon_order_item_id", $items->getOrderItemId($index));
                                }

                                // Adding custom stock for update
                                if ($mappedAttribute && $stockStatus) {
                                    if (isset($customStock[$sku])) {
                                        $customStock[$sku] += $qty;
                                    } else {
                                        $customStock[$sku] = $qty;
                                    }
                                }

                                // Adding order items in OrderItemCollection variable

                                $orderItemsCollection[$items->getOrderItemId($index)] = [
                                        "sku" => $sku,
                                        "asin" => $items->getASIN($index),
                                        "order_id" => $amazonOrderId,
                                        "order_item_id" => $items->getOrderItemId($index),
                                        "customized_url" => $items->getCustomizedURL($index),
                                        "qty_ordered" => $items->getQuantityOrdered($index),
                                        "qty_shipped" => $items->getQuantityShipped($index),
                                    ];
                                $itemAccepted++;
                            } else {
                                $reason[self::ERROR_OUT_OF_STOCK_CODE] =
                                    sprintf(self::ERROR_OUT_OF_STOCK, $item['SellerSKU']);
                            }
                        } else {
                            $reason[self::ERROR_NOT_ENABLED_CODE] =
                                sprintf(self::ERROR_NOT_ENABLED, $item['SellerSKU'], $store->getName());
                        }
                    } else {
                        $reason[self::ERROR_DOES_NOT_EXISTS_CODE] =
                            sprintf(self::ERROR_DOES_NOT_EXISTS, $item['SellerSKU'], $store->getName());
                    }
                } else {
                    $reason[self::ERROR_ITEM_DATA_NOT_AVAILABLE_CODE] =
                        sprintf(self::ERROR_ITEM_DATA_NOT_AVAILABLE, $sku, $qty);
                }
            }

            /** @var \Magento\Quote\Model\ResourceModel\Quote\Item[] $qouteItems */
            $qouteItems = $quote->getAllItems();

            // Condition for full order acknowledge. Partial not allowed as order update feature is not present.
            if ($itemAccepted > 0 && count($qouteItems) >= ($itemOrdered + $bundleItems)) {
                if (!empty($address)) {
                    $countryId = $this->getValue('CountryCode', $address, '');
                    $regionName = ucfirst(strtolower((string)$this->getValue('StateOrRegion', $address, '')));

                    // Magento treats 'Puerto Rico' as State while Amazon as Country
                    if ($countryId == "PR") {
                        $countryId = "US";
                        $regionName = "Puerto Rico";
                    }

                    $city = ucfirst(strtolower((string)$this->getValue('City', $address, '')));
                    $pin = $this->getValue('PostalCode', $address, '44800');

                    $regionId = $this->region($regionName, $city, $countryId, $pin);
                    $shipAddress = [
                        'firstname' => $this->getValue(0, $name, 'N/A'),
                        'lastname' => $this->getValue(1, $name, 'N/A'),
                        'street' => [
                            $this->getValue('AddressLine1', $address, 'N/A'),
                            $this->getValue('AddressLine2', $address, ''),
                            $this->getValue('AddressLine3', $address, '')
                        ],
                        'city' => $city,
                        'country_id' => $countryId,
                        'postcode' => $pin,
                        'telephone' => $this->getValue('Phone', $address, '+1123456789'),
                        'fax' => '',
                        'save_in_address_book' => 0
                    ];

                    if (isset($regionId)) {
                        $shipAddress['region_id'] = $regionId;
                        $shipAddress['region'] = $regionName;
                    } else {
                        $shipAddress['region_id'] = '';
                        $shipAddress['region'] = $regionName;
                    }
                } else {
                    // Address is hidden, Use default Address
                    $countryId = \Amazon\Sdk\Marketplace::getCodeByMarketplaceId($order->getMarketplaceId());
                    $cityList = [
                        "US" => [
                            "region" => "New York",
                            "city" => "New York City",
                            "postcode" => "10026"
                        ],
                        "CA" => [
                            "region" => "Ontario",
                            "city" => "Toronto",
                            "postcode" => "M4B1B5"
                        ],
                        "MX" => [
                            "region" => "Mexico",
                            "city" => "Mexico City",
                            "postcode" => "00810"
                        ],
                        "UK" => [
                            "region" => "London",
                            "city" => "London",
                            "postcode" => "E16AN"
                        ],
                    ];

                    if (isset($cityList[$countryId]["city"])) {
                        $city = $cityList[$countryId]["city"];
                        $regionName = $cityList[$countryId]["region"];
                        $pin = $cityList[$countryId]["postcode"];
                        $regionId = $this->region($regionName, $city, $countryId, $pin);
                        $shipAddress = [
                            'firstname' => $this->getValue(0, $name, 'N/A'),
                            'lastname' => $this->getValue(1, $name, 'N/A'),
                            'street' => [
                                "Hidden",
                                "Hidden",
                                "Hidden",
                            ],
                            'city' => $city,
                            'country_id' => $countryId,
                            'postcode' => $pin,
                            'fax' => '',
                            'save_in_address_book' => 0,
                            'telephone' => "+1123456789",
                        ];

                        if (isset($regionId)) {
                            $shipAddress['region_id'] = $regionId;
                            $shipAddress['region'] = $regionName;
                        } else {
                            $shipAddress['region_id'] = '';
                            $shipAddress['region'] = $regionName;
                        }
                    }
                }

                // Use default billing address.
                if (!empty($this->config->getUseDefaultBilling()) &&
                    $customer instanceof \Magento\Customer\Api\Data\CustomerInterface) {
                    try {
                        $billingAddressId = $customer->getDefaultBilling();
                        $billingAddress = $this->addressFactory->create()->load($billingAddressId);
                        // BUG: Cause exception if billing  country is not allowed on website
                        $quote->getBillingAddress()->addData($billingAddress->getData());
                    } catch (\Exception $e) {
                        $billAddress = $shipAddress;
                        $quote->getBillingAddress()->addData($billAddress);

                        $this->logger->error(
                            "Default biller email is invalid.",
                            [
                                'path' => __METHOD__,
                                'message' => $e->getMessage()
                            ]
                        );
                    }
                } else {
                    $billAddress = $shipAddress;
                    $quote->getBillingAddress()->addData($billAddress);
                }

                $shippingAddress = $quote->getShippingAddress()->addData($shipAddress);

                $this->amazonOrderRegistry->setCountryCode($countryId);

                $shippingTotal = ($shippingTotal >= $shippingDiscount) ?
                    ($shippingTotal - $shippingDiscount) : $shippingTotal;

                $this->registerShippingAmount($shippingTotal);
                $this->registerShippingMethod($order->getShipServiceLevel());

                $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
                    ->setShippingMethod($account->getShippingMethod());

                /** @var \Magento\Quote\Model\Quote\Address\Rate|null $rate */
                $rate = $shippingAddress->getShippingRateByCode($account->getShippingMethod());
                if (!$rate instanceof \Magento\Quote\Model\Quote\Address\Rate) {
                    $rate = $this->rateFactory->create();
                }

                $rate->setCode($account->getShippingMethod())
                    ->setMethod(\Ced\Amazon\Model\Carrier\Shipbyamazon::METHOD_CODE)
                    ->setMethodTitle($order->getShipServiceLevel())
                    ->setCarrier(\Ced\Amazon\Model\Carrier\Shipbyamazon::CARRIER_CODE)
                    ->setCarrierTitle(\Ced\Amazon\Model\Carrier\Shipbyamazon::CARRIER_TITLE)
                    ->setPrice($shippingTotal)
                    ->setAddress($shippingAddress);
                $shippingAddress->addShippingRate($rate);

                $quote->setPaymentMethod($account->getPaymentMethod());
                $quote->setInventoryProcessed(false);
                $quote->getPayment()->importData([
                    'method' => $account->getPaymentMethod()
                ]);
//                $quote->setExtensionAttributes(null);
//                $quote->setForcedCurrency(null);
//                $quote->setCurrency(null);
//                print_r($shippingAddress->getData('applied_taxes'));
//
//                die('3');

                $total = 0.00;

                /** @var \Magento\Quote\Model\Quote\Item $item */
                foreach ($quote->getAllItems() as $item) {
                    $qty = (float)$item->getQty();
                    $sku = $item->getSku();
                    $discountValue = isset($discount[$sku]) ? $discount[$sku] : 0.00;

                    if ($item->getParentItem() && $item->getParentItem()->getProductType() ==
                        \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                        $parentId = $item->getParentItem()->getProductId();

                        if (isset($quantity[$parentId])) {
                            $qty = $quantity[$parentId];
                          //  $item->setQtyToAdd($qty);
                            $quantity[$parentId] = 0;
                        }

                        if (isset($quoteItemPrice[$parentId])) {
                            $price = $quoteItemPrice[$parentId]['price'];
                            $baseprice = $quoteItemPrice[$parentId]['base_price'];
                            $rowTotal = $quoteItemPrice[$parentId]['row_total'];
                            $item->setPrice($price)
                                ->setTaxClassId($this->getTaxClassId())
                                ->setSpecialPrice($price)
                                ->setTierPrice([])
                                ->setBasePrice($baseprice)
                                ->setOriginalCustomPrice($price)
                                ->setRowTotal($rowTotal)
                                ->setBaseRowTotal($rowTotal)
                                ->setFinalPrice($rowTotal)
                                ->setBaseCalculationPrice($baseprice)
                                ->setTaxCalculationPrice($baseprice)
                                ->setBaseTaxCalculationPrice($baseprice)
                                ->setDiscountCalculationPrice($baseprice)
                                ->setBaseDiscountCalculationPrice($baseprice)
                                ->setConvertedPrice($price)
                                ->setPriceInclTax($price)
                                ->setRowTotalInclTax($rowTotal)
                                ->setBasePriceInclTax($price)
                                ->setBaseRowTotalInclTax($rowTotal);

                            $quoteItemPrice[$parentId]['price'] = 0;
                            $quoteItemPrice[$parentId]['base_price'] = 0;
                            $quoteItemPrice[$parentId]['row_total'] = 0;
                            $discountValue = isset($discount[$parentId]) ? $discount[$parentId] : 0.00;
                        }

                        if (isset($tax[$parentId])) {
                            $tax[$sku] = $tax[$parentId];
                            $tax[$parentId] = 0;
                        }
                    }

                    if ($item->getProductType() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                        $total += ($qty * $item->getPrice());
                    }
                    $item->setDiscountAmount($discountValue);
                    $item->setBaseDiscountAmount($discountValue);
                    $item->setOriginalCustomPrice($item->getPrice());
                    $item->setOriginalPrice($item->getPrice());

                    if (isset($tax[$sku]) && $tax[$sku] > 0) {
                        if ($importTax && $order->getMarketplaceId() == \Amazon\Sdk\Marketplace::MARKETPLACE_ID_US) {
                            if (isset($regionId) && in_array($regionId, $this->USTaxedRegions)) {
                                $value = $tax[$sku];
                                $totalTax += $value;

                                $itemTax = ($value / $qty);
                                $percentage = number_format(($itemTax / $item->getPrice() * 100), 6);
                                $percentage = $this->round($percentage);
                                $item->setTaxAmount($value);
                                $item->setTaxPercent($percentage);
                                $item->setBasePriceInclTax($item->getPrice() + $itemTax);
                                $item->setBasePrice($item->getPrice());
                                $item->setPrice($item->getPrice());
                                $item->setPriceInclTax($item->getPrice() + $itemTax);
                                $item->setRowTotal($item->getPrice() * $qty);
                                $item->setRowTotalInclTax(($item->getPrice() * $qty) + $value);
                            } else {
                                // No Tax Applied for Other Regions in US.
                                $item->setTaxAmount(0);
                                $item->setTaxPercent(0);
                            }
                        } else {
                            $value = $tax[$sku];

                            if ($item->getProductType() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                                $totalTax += $value;
                            }

                            $itemTax = ($value / $qty);
                            $percentage = number_format(($itemTax / $item->getPrice() * 100), 6);
                            $percentage = $this->round($percentage);
                            $item->setTaxAmount($value);
                            $item->setTaxPercent($percentage);
                            $item->setBasePriceInclTax($item->getPrice() + $itemTax);
                            $item->setBasePrice($item->getPrice());
                            $item->setPrice($item->getPrice());
                            $item->setPriceInclTax($item->getPrice() + $itemTax);
                            $item->setRowTotal($item->getPrice() * $qty);
                            $item->setRowTotalInclTax(($item->getPrice() * $qty) + $value);
                            $item->setBaseRowTotalInclTax(($item->getPrice() * $qty) + $value);
                        }
                    }

                    $item->save();
                }

                // Updating the reserve order id (increment id)
                $quote->reserveOrderId();
                $reservedOrderId = $quote->getReservedOrderId();
                $reservedOrderId = $this->generateIncrementId($reservedOrderId, $order);
                $quote->setReservedOrderId($reservedOrderId);

                if ($discountAmount > 0) {
                    $total = $quote->getBaseSubtotal();
                    $quote->collectTotals();
                    //$quote->setSubtotal(0);
                    //$quote->setBaseSubtotal(0);

                    //$quote->setSubtotalWithDiscount(0);
                    //$quote->setBaseSubtotalWithDiscount(0);

                    //$quote->setGrandTotal(0);
                    //$quote->setBaseGrandTotal(0);

                    $canAddItems = $quote->isVirtual() ? ('billing') : ('shipping');
                    foreach ($quote->getAllAddresses() as $address) {
                        //$address->setSubtotal(0);
                        //$address->setBaseSubtotal(0);

                        //$address->setGrandTotal(0);
                        //$address->setBaseGrandTotal(0);

                        //$address->collectTotals();

                        $quote->setSubtotal((float)$quote->getSubtotal() + $address->getSubtotal());
                        $quote->setBaseSubtotal((float)$quote->getBaseSubtotal() + $address->getBaseSubtotal());

                        $quote->setSubtotalWithDiscount(
                            (float)$quote->getSubtotalWithDiscount() + $address->getSubtotalWithDiscount()
                        );
                        $quote->setBaseSubtotalWithDiscount(
                            (float)$quote->getBaseSubtotalWithDiscount() + $address->getBaseSubtotalWithDiscount()
                        );

                        $quote->setGrandTotal((float)$quote->getGrandTotal() + $address->getGrandTotal());
                        $quote->setBaseGrandTotal((float)$quote->getBaseGrandTotal() + $address->getBaseGrandTotal());
                        $quote->save();

                        $quote->setGrandTotal($quote->getBaseSubtotal() - $discountAmount)
                            ->setBaseGrandTotal($quote->getBaseSubtotal() - $discountAmount)
                            ->setSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                            ->setBaseSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                            ->save();

                        if ($address->getAddressType() == $canAddItems) {
                            $address->setSubtotalWithDiscount((float)$address->getSubtotalWithDiscount() - $discountAmount);
                            $address->setGrandTotal((float)$address->getGrandTotal() - $discountAmount);
                            $address->setBaseSubtotalWithDiscount((float)$address->getBaseSubtotalWithDiscount() - $discountAmount);
                            $address->setBaseGrandTotal((float)$address->getBaseGrandTotal() - $discountAmount);
                            if ($address->getDiscountDescription()) {
                                $address->setDiscountAmount(-($address->getDiscountAmount() - $discountAmount));
                                $address->setDiscountDescription($address->getDiscountDescription() . ', Amazon Discount');
                                $address->setBaseDiscountAmount(-($address->getBaseDiscountAmount() - $discountAmount));
                            } else {
                                $address->setDiscountAmount(-($discountAmount));
                                $address->setDiscountDescription('Amazon Discount');
                                $address->setBaseDiscountAmount(-($discountAmount));
                            }

                            $address->save();
                        }//end: if
                    } //end: foreach

                    foreach ($quote->getAllItems() as $item) {
                        $sku = $item->getSku();
                        $discountValue = isset($discount[$sku]) ? $discount[$sku] : 0.00;
                        $item->setDiscountAmount($discountValue);
                        $item->setBaseDiscountAmount($discountValue)->save();
                    }
                } else {
                    $quote->setCouponCode('')->collectTotals()->save();
                }

//                $quote->setSubtotalWithDiscount($quote->getSubtotal() - $discountAmount);
//                $quote->setBaseSubtotalWithDiscount($quote->getSubtotal() - $discountAmount);

                // forcefully changing the currency for quote
                $quote->setForcedCurrency($directoryCurrency);
                $quote->setBaseCurrencyCode($order->getCurrencyCode());
                $quote->setGlobalCurrencyCode($order->getCurrencyCode());
                $quote->setStoreCurrencyCode($order->getCurrencyCode());
                $quote->setQuoteCurrencyCode($order->getCurrencyCode());
                $quote->setCurrency($quoteCurrency);

                try {
                    /** @var \Magento\Sales\Model\Order $magentoOrder */
                    $magentoOrder = $this->cartManagementInterface->submit($quote);
                } catch (\Exception $e) {
                    $magentoOrder = $this->checkIfExists($reservedOrderId);
                }
                if (isset($magentoOrder)) {
                    // 2019-06-20T14:23:49.894Z, use gmdate to avoid GMT add or delete hours
                    $createdAt = date("Y-m-d H:i:s", strtotime($order->getPurchaseDate()));
                    $magentoOrder
                        ->setCreatedAt($createdAt)
                        ->setBaseDiscountAmount($discountAmount)//-$discountAmount
                        ->setDiscountAmount($discountAmount)//-$discountAmount
                        ->setSubtotal($total)
                        ->setSubtotalInclTax($total + $totalTax)
                        ->setBaseSubtotalInclTax($total + $totalTax)
                        ->setGrandTotal($total + $totalTax - $discountAmount + $shippingTax + $shippingTotal)
                        ->setBaseGrandTotal($total + $totalTax - $discountAmount + $shippingTax + $shippingTotal)
                        ->setShippingInclTax($shippingTax + $shippingTotal)
                        ->setShippingTaxAmount($shippingTax)
                        ->setBaseShippingTaxAmount($shippingTax)
                        ->setShippingAmount($shippingTotal)
                        ->setBaseShippingAmount($shippingTotal)
                        ->setBaseShippingInclTax($shippingTax + $shippingTotal)
                        ->setShippingInclTax($shippingTax + $shippingTotal)
                        ->setTaxAmount($totalTax + $shippingTax)
                        ->setBaseTaxAmount($totalTax + $shippingTax);

//                    $incrementId = $this->generateIncrementId($magentoOrder->getIncrementId(), $order);
//                    $magentoOrder->setIncrementId($incrementId);

                    // Completing Order if Shipped
                    if ($order->getOrderStatus() == \Amazon\Sdk\Api\Order::ORDER_STATUS_SHIPPED) {
                        $magentoOrder->setState(\Magento\Sales\Model\Order::STATE_COMPLETE)
                            ->setStatus(\Magento\Sales\Model\Order::STATE_COMPLETE);
                    }

                    // Overriding order details. Data is different from events triggered.
                    $magentoOrder->save();

                    foreach ($magentoOrder->getAllItems() as $oItems) {
                        $orderItemsCollection[$oItems->getAmazonOrderItemId()]['magento_order_item_id'] = $oItems->getItemId();
                    }

                    $this->imported = isset($magentoOrder) ? $this->imported + 1 : $this->imported;

                    // after save order
                    $purchaseDate = date("Y-m-d H:i:s", strtotime($order->getPurchaseDate()));

                    $lastUpdateDate = date("Y-m-d H:i:s", strtotime($order->getLastUpdateDate()));

                    $status = $order->getOrderStatus();
                    $orderData = [
                        'amazon_order_id' => $order->getAmazonOrderId(),
                        'purchase_date' => $purchaseDate,
                        'last_update_date' => $lastUpdateDate,
                        'magento_order_id' => $magentoOrder->getId(),
                        'magento_increment_id' => $magentoOrder->getIncrementId(),
                        'status' => $status,
                        'order_data' => $this->json->jsonEncode($order->getData()),
                        'order_items' => $this->json->jsonEncode($items->getItems()), // TODO: Remove, use item table
                        \Ced\Amazon\Model\Order::COLUMN_FAILURE_REASON => $this->json->jsonEncode($reason),
                        'fulfillment_channel' => $order->getFulfillmentChannel(),
                        'sales_channel' => $order->getSalesChannel()
                    ];

                    $mporder = $this->orderFactory->create()->load($mporderId)->addData($orderData);
                    $mporder->save();

                    foreach ($orderItemsCollection as $orderSku => $orderItems) {
                        try {
                            /** @var \Ced\Amazon\Api\Data\Order\ItemInterface $orderItem */
                            $orderItem = $this->orderItemFactory->create();
                            $orderItem->setData($orderItems)->save();
                        } catch (\Exception $exception) {
                            $this->logger->error(
                                " Items of #{$amazonOrderId} import failure exception.",
                                [
                                    'path' => __METHOD__,
                                    'message' => $exception->getMessage(),
                                    'stack_trace' => $exception->getTraceAsString()
                                ]
                            );
                        }
                    }

                    if (!empty($customStock) && $mappedAttribute) {
                        foreach ($customStock as $productSku => $stockQuantity) {
                            // TODO: FIX: do not load the product again.
                            /** @var \Magento\Catalog\Model\Product $product */
                            $product = $this->productService->find($productSku);
                            $stockInAttribute = (int)$product->getData($mappedAttribute);
                            $product->setCustomAttribute($mappedAttribute, $stockInAttribute - $stockQuantity);
                            $this->productService->update($product, [$mappedAttribute]);
                        }
                    }

                    // Adding extension attributes and dispatching import after event
                    $magentoOrder->getExtensionAttributes()
                        ->setAmazonOrderId($amazonOrderId)
                        ->setAmazonOrderPlaceDate($purchaseDate);
                    $this->_eventManager->dispatch("amazon_order_import_after", ['order' => $magentoOrder]);

                    // Notifying store admin via email
                    $adminEmail = $this->config->getNotificationEmail();
                    if (!empty($adminEmail)) {
                        /** @var \Magento\Framework\DataObject $data */
                        $data = $this->dataFactory->create();
                        $data->addData([
                            'to' => $adminEmail,
                            'marketplace_name' => 'Amazon',
                            'po_id' => $order->getAmazonOrderId(),
                            'order_id' => $magentoOrder->getIncrementId(),
                            'order_date' => $purchaseDate,
                        ]);
                        /** @var \Ced\Amazon\Model\Mail $mail */
                        $mail = $this->mailFactory->create();
                        $mail->send($data);
                    }

                    $autoInvoice = $this->config->getAutoInvoice();
                    if ($autoInvoice) {
                        $this->invoice($magentoOrder);
                    }

                    $autoAcknowledge = $this->config->getAutoAcknowledgement();
                    if ($autoAcknowledge) {
                        $this->acknowledge($magentoOrder->getId(), $order->getAmazonOrderId(), $mporder, $account);
                    }

                    if ($this->config->getInventorySync()) {
                        $this->inventory->update($productIds, true, \Ced\Amazon\Model\Source\Queue\Priorty::HIGH);
                    }
                } else {
                    throw new LocalizedException(__('Failed to create order in Magento.'));
                }
            } else {
                $this->reject($order, $items->getItems(), $reason, $account);
            }
        } catch (\Exception $exception) {
            // Save Order
            $reason[self::ERROR_ORDER_IMPORT_EXCEPTION_CODE] =
                self::ERROR_ORDER_IMPORT_EXCEPTION . ' [' . $exception->getMessage() . ' ]';
            $items = isset($items) ? $items->getItems() : [];
            $this->save($order, $reason, $items);

            // Removing quote on failure
            if (isset($quote) && $quote instanceof CartInterface) {
                $this->cartRepositoryInterface->delete($quote);
            }

            // Add Logging
            $level = $this->config->getLoggingLevel();
            $log = [
                'path' => __METHOD__,
                'message' => $exception->getMessage(),
            ];
            if ($level < 100) {
                $log['trace'] = $exception->getTraceAsString();
            }
            $this->logger->error("Order #{$amazonOrderId} import failure exception.", $log);
            return false;
        }

        return true;
    }

    /**
     * Check if order exists
     * @param string $incrementId
     * @return \Magento\Framework\DataObject|null
     */
    private function checkIfExists($incrementId)
    {
        $result = null;
        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection */
        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter('increment_id', ['eq' => $incrementId]);
        $collection->addFieldToSelect(['entity_id', 'increment_id']);
        $collection->setCurPage(1);
        $collection->setPageSize(1);
        if ($collection->getSize() > 0) {
            $result = $collection->getFirstItem();
        }

        return $result;
    }

    /**
     * Check if item line is cancelled
     * @param $check
     * @param array $items
     * @return array
     */
    private function checkIfCancelled($check, $items = [])
    {
        foreach ($items as $item) {
            if (isset($check['SellerSKU'], $check['QuantityOrdered'], $item['SellerSKU'], $item['QuantityOrdered']) &&
                $item['SellerSKU'] == $check['SellerSKU'] &&
                $item['QuantityOrdered'] < $check['QuantityOrdered']
            ) {
                $check['QuantityOrdered'] = $item['QuantityOrdered'];
            }
        }

        return $check;
    }

    private function getTaxClassId()
    {
        return 0;

        // TODO: WIP

        /** @var \Magento\Tax\Model\ClassModel $class */
        $class = $this->objectManager->create(\Magento\Tax\Model\ClassModel::class);
        $class->getCollection()->addFieldToFilter(\Magento\Tax\Model\ClassModel::KEY_NAME, 'Amazon MWS')
            ->getFirstItem();

        if ($class->getId() > 0) {
        } else {
            $class->setData(\Magento\Tax\Model\ClassModel::KEY_NAME, 'Amazon MWS');
            $class->setData(
                \Magento\Tax\Model\ClassModel::KEY_TYPE,
                \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT
            );
        }
        /** @var \Magento\Tax\Model\Calculation\Rule $rule */
        $rule = $this->objectManager->create(\Magento\Tax\Model\Calculation\Rule::class);
        $rule->setCode("Amazon MWS");
        $rule->setPriority(0);
        $rule->setCustomerTaxClassIds([3]);
        $rule->setProductTaxClassIds([2]);
        $rule->setTaxRateIds([3]);
        $rule->save();

        return $rule->getId();
    }

    /**
     * Save Order in Amazon
     * @param \Amazon\Sdk\Api\Order $order
     * @param array $reason
     * @param array $items
     * @return \Ced\Amazon\Model\Order
     * @throws \Exception
     */
    public function save(\Amazon\Sdk\Api\Order $order, $reason = [], $items = [])
    {
        /** @var \Ced\Amazon\Model\Order $mporder */
        $mporder = $this->orderFactory->create()
            ->load($order->getAmazonOrderId(), \Ced\Amazon\Model\Order::COLUMN_PO_ID);
        $mporder->setData(\Ced\Amazon\Model\Order::COLUMN_STATUS, \Ced\Amazon\Model\Source\Order\Status::FAILED);
        $mporder->setData(
            \Ced\Amazon\Model\Order::COLUMN_FAILURE_REASON,
            $this->json->jsonEncode($reason)
        );
        $mporder->setData(
            \Ced\Amazon\Model\Order::COLUMN_ORDER_ITEMS,
            $this->json->jsonEncode($items)
        );

        return $mporder->save();
    }

    /**
     * //@TODO recheck
     * @param \Amazon\Sdk\Api\Order $order
     * @param array $items
     * @param array $reason
     * @param \Ced\Amazon\Model\Account|null $account
     * @return bool
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function reject(\Amazon\Sdk\Api\Order $order, $items = [], $reason = [], $account = null)
    {
        $response = false;
        if ($this->config->getAutoCancellation() && isset($account)) {
            $acknowledgement = $this->objectManager->create(\Amazon\Sdk\Order\Acknowledgement::class);
            $acknowledgement->setId($order->getSellerOrderId());
            $acknowledgement->setData(
                $order->getAmazonOrderId(),
                \Amazon\Sdk\Order\Acknowledgement::STATUS_CODE_FAILURE
            );

            $envelope = $this->objectManager->create(
                \Amazon\Sdk\Envelope::class,
                [
                    'merchantIdentifier' => $account->getConfig()->getSellerId(),
                    'messageType' => \Amazon\Sdk\Base::MESSAGE_TYPE_ORDER_ACKNOWLEDGEMENT
                ]
            );
            $envelope->addAcknowledgement($acknowledgement);
            $response = $this->feed->send(
                $envelope,
                [
                    'type' => \Amazon\Sdk\Api\Feed::ORDER_ACKNOWLEDGEMENT,
                    'account_id' => $account->getId(),
                    'ids' => [],
                ]
            );
        }

        $mporder = $this->save($order, $reason, $items);

        if ($response !== false) {
            $mporder
                ->setData(\Ced\Amazon\Model\Order::COLUMN_STATUS, \Ced\Amazon\Model\Source\Order\Status::CANCELLED);
            $mporder->save();
        }

        $this->logger->addNotice(
            'Order import failed. Order Id: #' . $order->getAmazonOrderId(),
            [
                'cancelled' => $response,
                'reason' => $reason,
            ]
        );
        return true;
    }

    /**
     * Get value from an array
     * @param string|int $index
     * @param array $haystack
     * @param string|null $default
     * @return null|string
     */
    public function getValue($index, $haystack = [], $default = null)
    {
        $value = $default;
        if (isset($index, $haystack[$index]) && !empty($haystack[$index])) {
            $value = $haystack[$index];
        }
        return $value;
    }

    /**
     * Generate Invoice
     * @param \Magento\Sales\Model\Order $order
     * @throws LocalizedException
     * @deprecated : Payment method auto invoice
     */
    public function invoice($order)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $this->objectManager->create(\Magento\Sales\Model\Service\InvoiceService::class)
            ->prepareInvoice($order);
        //$invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->pay()->save();
        $transactionSave = $this->objectManager->create(\Magento\Framework\DB\Transaction::class)
            ->addObject($invoice)->addObject($invoice->getOrder());
        $transactionSave->save();

        $orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;
        $order->setState($orderState)->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
        $order->addStatusToHistory(
            $order->getStatus(),
            __('\'Order invoiced #%1 automatically after import.', $invoice->getId())
        );
        $order->save();
    }

    /**
     * @TODO add cancel items
     * Acknowledge order on Amazon
     * @param string $orderId
     * @param string $amazonOrderId
     * @param \Ced\Amazon\Model\Order $mporder
     * @param \Ced\Amazon\Api\Data\AccountInterface|null $account
     * @return array|bool
     * @throws \Exception
     */
    public function acknowledge($orderId, $amazonOrderId, $mporder = null, $account = null)
    {
        // Getting Order
        if (!isset($mporder)) {
            $mporder = $this->orderFactory->create()->load(
                $amazonOrderId,
                \Ced\Amazon\Model\Order::COLUMN_PO_ID
            );
        }

        // Getting Account
        if (!isset($account)) {
            $account = $this->accountRepository->getById($mporder->getAccountId());
        }

        // Acknowledging order on Amazon
        $acknowledgement = $this->objectManager->create(\Amazon\Sdk\Order\Acknowledgement::class);
        $acknowledgement->setId($orderId);
        $acknowledgement->setData($amazonOrderId, \Amazon\Sdk\Order\Acknowledgement::STATUS_CODE_SUCCESS);
        $envelope = $this->objectManager->create(
            \Amazon\Sdk\Envelope::class,
            [
                'merchantIdentifier' => $account->getData(\Ced\Amazon\Model\Account::COLUMN_SELLER_ID),
                'messageType' => \Amazon\Sdk\Base::MESSAGE_TYPE_ORDER_ACKNOWLEDGEMENT
            ]
        );
        $envelope->addAcknowledgement($acknowledgement);
        $response = $this->feed->send(
            $envelope,
            [
                'type' => \Amazon\Sdk\Api\Feed::ORDER_ACKNOWLEDGEMENT,
                'account_id' => $account->getId(),
                'ids' => [$orderId]
            ]
        );

        // Updating status in Amazon order table
        if (!empty($mporder) && $mporder->getId()) {
            $mporder->setData(
                \Ced\Amazon\Model\Order::COLUMN_STATUS,
                \Ced\Amazon\Model\Source\Order\Status::ACKNOWLEDGED
            )
                ->save();
        }

        return $response;
    }

    /**
     * Add Admin Notification
     * @param string $title
     * @param string $message
     * @param string $type
     */
    public function notify($title = "", $message = "", $type = 'notice')
    {
        if ($this->config->addNotification()) {
            if ($type == "critical") {
                $this->notifier->addCritical($title, $message);
            } else {
                $this->notifier->addNotice($title, $message);
            }
        }
    }

    /**
     * @param $string
     * @return bool
     * @deprecated
     */
    public function validateString($string)
    {
        $stringValidation = (isset($string) && !empty($string)) ? true : false;
        return $stringValidation;
    }

    /**
     * @param array $data
     * @return array
     * @deprecated
     * @TODO: move generateShipment to shipment helper
     * Ship Amazon Order
     */
    public function fulfill(array $data = [])
    {
        $response = [
            'success' => false,
            'message' => []
        ];

        try {
            /** @var \Amazon\Sdk\Order\Fulfillment $fulfillment */
            $fulfillment = $this->objectManager->create(\Amazon\Sdk\Order\Fulfillment::class);

            if (isset($data['OrderId']) && !empty($data['OrderId'])) {
                $fulfillment->setId($data['OrderId']);
                // Saving fulfillment data.
                /** @var \Ced\Amazon\Model\Order $mporder */
                $mporder = $this->orderFactory->create()->load($data['OrderId'], 'magento_order_id');
                /** @var \Ced\Amazon\Model\Account $account */
                $account = $this->accountRepository->getById(
                    $mporder->getData(\Ced\Amazon\Model\Order::COLUMN_ACCOUNT_ID)
                );
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__('Order Id is invalid.'));
            }

            if (isset($data['AmazonOrderID']) && !empty($data['AmazonOrderID'])) {
                $fulfillment->setData($data['AmazonOrderID'], $data);
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__('AmazonOrderID is invalid.'));
            }

            if (isset($data['Items']) && !empty($data['Items'])) {
                $fulfillment->setItems($data['Items']);
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__('Items are missing.'));
            }

            /** @var \Amazon\Sdk\Validator $validator */
            $validator = $this->objectManager->create(
                \Amazon\Sdk\Validator::class,
                ['object' => $fulfillment]
            );

            if ($validator->validate()) {
                $envelope = $this->objectManager->create(
                    \Amazon\Sdk\Envelope::class,
                    [
                        'merchantIdentifier' => $account->getConfig()->getSellerId(),
                        'messageType' => \Amazon\Sdk\Base::MESSAGE_TYPE_ORDER_FULFILLMENT
                    ]
                );
                $envelope->addFulfillment($fulfillment);
                $feed = $this->feed->send(
                    $envelope,
                    [
                        'type' => \Amazon\Sdk\Api\Feed::ORDER_FULFILLMENT,
                        'account_id' => $account->getId(),
                        'ids' => []
                    ]
                );
                $this->generateShipment($data['OrderId'], $data['Items']);
                $response['success'] = true;
                $response['message'] = $feed;

                $data['Status'] = \Ced\Amazon\Model\Source\Feed\Status::SUBMITTED;
                $data['Feed'] = $feed;

                $fulfillments = [];
                if (!empty($mporder->getData('fulfillments'))) {
                    $fulfillments = $this->json->jsonDecode($mporder->getData('fulfillments'));
                }
                $fulfillments[] = $data;

                $mporder->setData('fulfillments', $this->json->jsonEncode($fulfillments));

                //@TODO: check if partially shipped.
                $mporder->setData('status', \Ced\Amazon\Model\Source\Order\Status::SHIPPED);
                $mporder->save();
            } else {
                $response['message'] = $validator->getErrors();
            }
        } catch (\Exception $exception) {
            $response['message'] = $exception->getMessage();
        }
        return $response;
    }

    /**
     * @param $orderId
     * @param $items
     * @throws LocalizedException
     * @deprecated: Moved to shipment
     */
    public function generateShipment($orderId, $items)
    {
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class)->load($orderId);
        if (isset($order) && !empty($order)) {
            $shipItems = [];
            foreach ($order->getAllItems() as $orderItem) {
                foreach ($items as $item) {
                    if ($orderItem->getSku() == $item['SKU']) {
                        $shipItems[$orderItem->getId()] = $item['Quantity'];
                    }
                }
            }

            $shipment = $this->prepareShipment($order, $shipItems);
            if ($shipment) {
                $shipment->register();
                $shipment->getOrder()->setIsInProcess(true);
                try {
                    $transactionSave = $this->objectManager->create(\Magento\Framework\DB\Transaction::class)
                        ->addObject($shipment)->addObject($shipment->getOrder());
                    $transactionSave->save();
                    $order->setStatus('complete')->save();
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage('Error in saving shipping:' . $e->getMessage());
                }
            }
        }
    }

    /**
     * @param $order
     * @param $items
     * @return bool|\Magento\Sales\Model\Order\Shipment
     * @deprecated: Moved to shipment
     */
    public function prepareShipment($order, $items)
    {
        $shipment = $this->objectManager->get(\Magento\Sales\Model\Order\ShipmentFactory::class)
            ->create($order, isset($items) ? $items : [], []);
        if (!$shipment->getTotalQty()) {
            return false;
        }
        return $shipment;
    }

    /**
     * Cancel Amazon Order
     * @param array $data
     * @return array
     * @deprecated: Moved to shipment
     */
    public function adjust(array $data = [])
    {
        $response = [
            'success' => false,
            'message' => []
        ];

        try {
            if (isset($data['AdjustedItems']) && !empty($data['AdjustedItems'])) {
                $adjustment = $this->objectManager->create(\Amazon\Sdk\Order\Adjustment::class);

                if (isset($data['OrderId']) && !empty($data['OrderId'])) {
                    $adjustment->setId($data['OrderId']);
                    /** @var \Ced\Amazon\Model\Order $mporder */
                    $mporder = $this->orderFactory->create()->load($data['OrderId'], 'magento_order_id');
                    /** @var \Ced\Amazon\Model\Account $account */
                    $account = $this->accountRepository->getById(
                        $mporder->getData(\Ced\Amazon\Model\Order::COLUMN_ACCOUNT_ID)
                    );
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Order Id is invalid.'));
                }

                if (isset($data['AmazonOrderID']) && !empty($data['AmazonOrderID'])) {
                    $adjustment->setData($data['AmazonOrderID'], $data);
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(__('AmazonOrderID is invalid.'));
                }

                if (isset($data['AdjustedItems']) && !empty($data['AdjustedItems'])) {
                    $adjustment->setItems($data['AdjustedItems']);
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(__('AdjustedItems are missing.'));
                }

                $validator = $this->objectManager->create(\Amazon\Sdk\Validator::class, ['object' => $adjustment]);
                if ($validator->validate()) {
                    $envelope = $this->objectManager->create(
                        \Amazon\Sdk\Envelope::class,
                        [
                            'merchantIdentifier' => $account->getConfig()->getSellerId(),
                            'messageType' => \Amazon\Sdk\Base::MESSAGE_TYPE_ORDER_ADJUSTMENT
                        ]
                    );
                    $envelope->addAdjustment($adjustment);
                    $feed = $this->feed->send(
                        $envelope,
                        [
                            'type' => \Amazon\Sdk\Api\Feed::ORDER_PAYMENT_ADJUSTMENT,
                            'account_id' => $account->getId(),
                            'ids' => []
                        ]
                    );
                    $this->generateCreditMemo($data['OrderId'], $data['AdjustedItems']);
                    $response['success'] = true;
                    $response['message'] = $feed;

                    // Saving adjustment data.
                    $orders = $this->orderFactory->create()->load($data['OrderId'], 'magento_order_id');

                    $data['Status'] = \Ced\Amazon\Model\Source\Feed\Status::SUBMITTED;
                    $data['Feed'] = $feed;

                    $adjustments = [];

                    if (!empty($orders->getData('adjustments'))) {
                        $adjustments = $this->json->jsonDecode($orders->getData('adjustments'));
                    }
                    $adjustments[] = $data;

                    $orders->setData('adjustments', $this->json->jsonEncode($adjustments));

                    //@TODO: check if partially shipped.
                    $orders->setData('status', \Ced\Amazon\Model\Source\Order\Status::SHIPPED);
                    $orders->save();
                } else {
                    $response['message'] = $validator->getErrors();
                }
            }
        } catch (\Exception $exception) {
            $response['message'] = $exception->getMessage();
        }

        return $response;
    }

    /**
     * @param $orderId
     * @param $items
     */
    public function generateCreditMemo($orderId, $items)
    {
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class)->load($orderId);
        if (isset($order) && !empty($order)) {
            $cancelledItems = [];
            foreach ($order->getAllItems() as $orderItem) {
                foreach ($items as $item) {
                    if ($orderItem->getSku() == $item['SKU']) {
                        $cancelledItems[$orderItem->getId()] = ['qty' => $item['QuantityCancelled']];
                    }
                }
            }

            $creditMemoLoader = $this->creditmemoLoaderFactory->create();
            $creditMemoLoader->setOrderId($order->getId());

            $data = [
                'items' => $cancelledItems,
                'do_offline' => '1',
                'comment_text' => 'Amazon Cancelled Orders',
                'adjustment_positive' => '0',
                'adjustment_negative' => '0'
            ];
            $creditMemoLoader->setCreditmemo($data);
            $creditMemo = $creditMemoLoader->load();
            $creditmemoManagement = $this->objectManager
                ->create(\Magento\Sales\Api\CreditmemoManagementInterface::class);
            if ($creditMemo) {
                $creditMemo->setOfflineRequested(true);
                $creditmemoManagement->refund($creditMemo, true);
            }
        }
    }

    /**
     * @param $orderModelData
     * @return array|bool
     */
    public function getShippedCancelledQty($orderModelData)
    {
        $serialized = 'check';
        $shipdata = [];
        foreach ($orderModelData as $order) {
            $serialized = $order->getShipmentData();
        }
        $serialized = $serialized == 'check' ? $orderModelData->getShipmentData() : $serialized;
        if (isset($serialized)) {
            $shipData = json_decode($serialized, true);
            if (isset($shipData) ? $shipData : false) {
                foreach ($shipData as $value) {
                    foreach ($value["items"] as $val) {
                        if (isset($shipdata[$val["sku"]])) {
                            $shipdata[$val["sku"]]['cancel_quantity'] += $val["cancel_quantity"];
                            $shipdata[$val["sku"]]['ship_qty'] += $val["ship_qty"];
                        } else {
                            $shipdata[$val["sku"]]['cancel_quantity'] = $val["cancel_quantity"];
                            $shipdata[$val["sku"]]['ship_qty'] = $val["ship_qty"];
                        }
                    }
                }
                return $shipdata;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $orderModelData
     * @return array|bool
     */
    public function getOrderedCancelledQty($orderModelData)
    {
        foreach ($orderModelData as $order) {
            $serializedOrderData = $order->getOrderData();
        }
        if (isset($serializedOrderData)) {
            $orderData = json_decode($serializedOrderData, true);
            if (isset($orderData)) {
                $orderItemsInfo = [];
                foreach ($orderData['items'] as $sdata) {
                    $orderItemsInfo[$sdata['SellerSKU']]['request_cancel_qty'] = 0;
                    $orderItemsInfo[$sdata['SellerSKU']]['request_sku_quantity'] = $sdata['QuantityOrdered'];
                }
                return $orderItemsInfo;
            }
        }
        return false;
    }

    public function processOrderItems(array $orderItems = [], array $fulfillments = [], array $adjustments = [])
    {
        $cancelledIds = [];
        $shippedIds = [];
        $items = [];

        foreach ($fulfillments as $fulfillment) {
            if (isset($fulfillment['Items'])) {
                foreach ($fulfillment['Items'] as $ids) {
                    $shippedIds[] = $ids['AmazonOrderItemCode'];
                }
            }
        }

        foreach ($adjustments as $adjustment) {
            foreach ($adjustment['AdjustedItems'] as $ids) {
                $cancelledIds[] = $ids['AmazonOrderItemCode'];
            }
        }

        foreach ($orderItems as $item) {
            if (!in_array($item['OrderItemId'], $shippedIds) && !in_array($item['OrderItemId'], $cancelledIds)) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Create Magento Increment Id by Rules
     * @param string $incrementId
     * @param \Amazon\Sdk\Api\Order $order
     * @return  string, Ex: AMZ-US111-111111-111111
     */
    public function generateIncrementId($incrementId, $order)
    {
        /** @var array $rules */
        $rules = $this->config->getIncrementIdRules();

        if (in_array(\Ced\Amazon\Model\Source\Order\Config\IncrementId::ADD_AMAZON_ORDER_ID, $rules)) {
            $incrementId = $order->getAmazonOrderId();
        }

        if (in_array(\Ced\Amazon\Model\Source\Order\Config\IncrementId::ADD_MARKETPLACE_CODE, $rules)) {
            $marketplaceId = $order->getMarketplaceId();
            $code = \Amazon\Sdk\Marketplace::getCodeByMarketplaceId($marketplaceId);
            if (!empty($code)) {
                $incrementId = $code . $incrementId;
            }
        }

        if (in_array(\Ced\Amazon\Model\Source\Order\Config\IncrementId::ADD_FULFILLMENT_CHANNEL, $rules)) {
            $channelPrefix = $order->getFulfillmentChannel();
            if (!empty($channelPrefix)) {
                $incrementId = $channelPrefix . '-' . $incrementId;
            }
        }

        if (in_array(\Ced\Amazon\Model\Source\Order\Config\IncrementId::ADD_PREFIX, $rules)) {
            $prefix = $this->config->getOrderIdPrefix();
            if (!empty($prefix)) {
                $incrementId = $prefix . $incrementId;
            }
        }

        return $incrementId;
    }

    /**
     * round off
     * 20.02 => 20.00
     * 20.499 => 20.50
     * 20.994 => 21.00
     * @param $value
     * @return float
     */
    public function round($value)
    {
        $rounded = round($value * 10);
        return number_format($rounded / 10, 2);
    }

    /**
     * Init order import
     */
    public function init()
    {
        $this->processTaxRegions();
    }
}
