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

namespace Ced\Cdiscount\Helper;

use Magento\Quote\Model\Quote;
use Sdk\Order\OrderLine;

/**
 * Class Order
 *
 * @package Ced\Cdiscount\Helper
 */
class Order extends \Magento\Framework\App\Helper\AbstractHelper
{

    const SHIPMENT_PROVIDERS = [
        'UPS',
        'USPS',
        'FedEx',
        'Airborne',
        'OnTrac',
        'Relais Colis',
        'TNT',
        'PostNL',
        'Mondial Relay',
        'La Poste - Courrier',
        'DPD',
        'GLS',
        'DHL Express',
        'DHL Deutsche Post',
        'China Post',
        'Chronopost',
        'Colissimo',
        'Colis Privé',
        'Bpost',
        'China EMS (ePacket)',
        'FedEx',
        'SF Express',
        'Singapore Post',
        '4PX',
        'Malaysia Post',
        'Yanwen',
        'CNE Express',
        'SFC Service',
        'GEODIS',
        'WishPost',
        'Asendia HK',
        'DB Schenker',
        'DHL ecommerce asia'
    ];

    const DEFAULT_EMAIL = 'customer@cdiscount.com';

    /**
     * @var \Magento\Framework\objectManagerInterface
     */
    public $objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    public $customerFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    public $customerRepository;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    public $productRepository;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $product;

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
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    public $cache;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    public $stockRegistry;

    /**
     * @var \Ced\Cdiscount\Model\Orders
     */
    public $orders;

    /**
     * @var \Magento\AdminNotification\Model\Inbox
     */
    public $inbox;

    /**
     * @var
     */
    public $messageManager;

    /**
     * @var \Ced\Cdiscount\Model\OrderFailed
     */
    public $orderFailed;

    public $orderValidationFactory;

    public $cdiscount;

    public $orderList;
    public $orderLineList;
    public $validateOrderLineFactory;

    /**
     * @var $config
     */
    public $config;

    /**
     * @var Logger
     */
    public $logger;

    /**
     * \
     *
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * Ids of Products
     *
     * @var array $ids
     */
    public $ids = [];

    /**
     * @var \Ced\Cdiscount\Model\FeedsFactory
     */
    public $feeds;

    public $taxCalculation;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $dateTime;

    public $directoryList;

    public $orderFilter;

    public $sizesFactory;

    public $parser;

    /**
     * Order constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\objectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Magento\Framework\Json\Helper\Data $json
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\Service\OrderService $orderService
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     * @param \Magento\Framework\App\Cache\TypeListInterface $cache
     * @param \Magento\AdminNotification\Model\Inbox $inbox
     * @param \Magento\Framework\Message\ManagerInterface $manager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Ced\Cdiscount\Model\OrdersFactory $orders
     * @param \Ced\Cdiscount\Model\FeedsFactory $feedsFactory
     * @param \Ced\Cdiscount\Model\OrderFailedFactory $orderFailed
     * @param Config $config
     * @param Logger $logger
     * @param \Sdk\ApiClient\CDSApiClientFactory $apiClientFactory
     * @param \Sdk\Order\OrderFilter $orderFilter
     * @param \Sdk\Order\Validate\ValidateOrderFactory $orderValidationFactory
     * @param \Sdk\Order\OrderList $orderList
     * @param \Sdk\Order\OrderLineList $orderLineList
     * @param \Sdk\Order\Validate\ValidateOrderLineFactory $validateOrderLineFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\objectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Framework\Registry $registry,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculation,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Framework\App\Cache\TypeListInterface $cache,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\AdminNotification\Model\Inbox $inbox,
        \Magento\Framework\Message\ManagerInterface $manager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Ced\Cdiscount\Model\OrdersFactory $orders,
        \Ced\Cdiscount\Model\FeedsFactory $feedsFactory,
        \Ced\Cdiscount\Model\OrderFailedFactory $orderFailed,
        \Ced\Cdiscount\Helper\Config $config,
        \Ced\Cdiscount\Helper\Logger $logger,
        \Ced\Cdiscount\Model\SizesFactory $sizesFactory,
        \Magento\Framework\Xml\ParserFactory $parser,
        \Sdk\ApiClient\CDSApiClientFactory $apiClientFactory,
        \Sdk\Order\OrderFilter $orderFilter,
        \Sdk\Order\Validate\ValidateOrderFactory $orderValidationFactory,
        \Sdk\Order\OrderList $orderList,
        \Sdk\Order\OrderLineList $orderLineList,
        \Sdk\Order\Validate\ValidateOrderLineFactory $validateOrderLineFactory
    ) {
        parent::__construct($context);
        $this->objectManager = $objectManager;
        $this->directoryList = $directoryList;
        $this->taxCalculation = $taxCalculation;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->sizesFactory = $sizesFactory;
        $this->product = $product;
        $this->json = $json;
        $this->orderService = $orderService;
        $this->creditmemoLoaderFactory = $creditmemoLoaderFactory;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->cache = $cache;
        $this->inbox = $inbox;
        $this->messageManager = $manager;
        $this->stockRegistry = $stockRegistry;
        $this->orders = $orders;
        $this->registry = $registry;
        $this->dateTime = $dateTime;
        $this->cdiscount = $apiClientFactory;
        $this->parser = $parser;
        $this->orderFailed = $orderFailed;
        $this->feeds = $feedsFactory;
        $this->logger = $logger;
        $this->config = $config;
        $this->orderFilter = $orderFilter;
        $this->orderValidationFactory = $orderValidationFactory;
        $this->orderList = $orderList;
        $this->orderLineList = $orderLineList;
        $this->validateOrderLineFactory = $validateOrderLineFactory;
    }

    /**
     * @return bool
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function importOrders()
    {
        try {
//            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//            $registry = $objectManager->get('Magento\Framework\Registry');
//
//            $id = 114167; // your order_id
//            $order = $objectManager->create('Magento\Sales\Model\Order')->load($id);
//
//// $incrementId = 'xxxxxxxxx';
// $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId('CD-1000151568-19120422492M1V2');
// print_r($order->getShippingAddress()->getData());
//
//            $registry->register('isSecureArea','true');
//            $order->delete();
//            $registry->unregister('isSecureArea');
//
//            die('adc');
            $storeId = $this->config->getStore();
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $store = $this->storeManager->getStore($storeId);
            $username = $this->config->getUserName();
            $password = $this->config->getUserPassword();
            $orderList = $this->cdiscount->create(
                [
                    'username' => $username,
                    'password' => $password
                ]
            );
            $token = $orderList->init();
            if (empty($token)) {
                return false;
            }
            $ordersPoint = $orderList->getOrderPoint();
            $orderFilter = $this->orderFilter;
            $orderFilter->setFetchOrderLines(true);
            $orderFilter->addState(\Sdk\Order\OrderStateEnum::WaitingForShipmentAcceptation);
//            $orderFilter->addOrderReferenceToList('19120422492M1V2');
            $orderListResponse = $ordersPoint->getOrderList($orderFilter);
            if ($orderListResponse->hasError()) {
                return false;
            }
            $orders = $orderListResponse->getOrderList()->getOrders();
            if (empty($orders)) {
                return false;
            }
            $count = 0;
//            print_r($orders);die('orders');
            if (isset($orders)) {
                foreach ($orders as $order) {
                    $cdiscountOrderId = $order->getOrderNumber();
                    $cdiscountOrder = $this->orders->create()
                        ->getCollection()
                        ->addFieldToFilter('cdiscount_order_id', $cdiscountOrderId);
                    if (!$this->validateString($cdiscountOrder->getData())) {
                        $customer = $this->getCustomer($order, $websiteId);
                        if ($customer !== false) {
                            //$this->acknowledgeOrders($order);
                            $count = $this->generateQuote($store, $customer, $order, $count);
                        } else {
                            continue;
                        }
                    }
                }
            }
            if ($count > 0) {
                $this->notificationSuccess($count);
                return true;
            }
        } catch (\Exception $exception) {
            if ($this->config->getDebugMode() == true) {
                $this->logger->error($exception->getMessage(), ['path' => __METHOD__,
                    'trace' => $exception->getTraceAsString()]);
            }
        }
        return false;
    }

    /**
     * @param $string
     * @return bool
     */
    public function validateString($string)
    {
        $stringValidation = (isset($string) && !empty($string)) ? true : false;
        return $stringValidation;
    }

    public function getEmail($order)
    {
        $email = $order->getCustomer()->getEmail();
        $encryptedEmail = $order->getCustomer()->getEncryptedEmail();
        if (!isset($email['nil']) and !empty($email)) {
            return $email;
        } elseif (!isset($encryptedEmail['nil'])
            and !empty($encryptedEmail)
        ) {
            return $encryptedEmail;
        } else {
            return self::DEFAULT_EMAIL;
        }
    }

    /**
     * @param $order
     * @param $websiteId
     * @return bool|\Magento\Customer\Model\Customer
     */
    public function getCustomer($order, $websiteId)
    {
        $customerId = $this->config->getDefaultCustomer();
        $billing = $order->getBillingAddress();

        if ($customerId !== false) {
            // Case 1: Use default customer.
            $customer = $this->customerFactory->create()
                ->setWebsiteId($websiteId)
                ->load($customerId);
            if (!isset($customer) or empty($customer)) {
                $this->logger->log(
                    'ERROR',
                    "Default Customer does not exists. Customer Id: #{$customerId}."
                );
                return false;
            }
        } else {
            // Case 2: Use Customer from Order.
            $email = $this->getEmail($order);
            // Case 2.1 Get Customer if already exists.
            $customer = $this->customerFactory->create()
                ->setWebsiteId($websiteId)
                ->loadByEmail($email);

            if (!isset($customer) or empty($customer) or empty($customer->getData())) {
                // Case 2.1 : Create customer if does not exists.
                try {
                    $customer = $this->customerFactory->create();
                    $customer->setWebsiteId($websiteId);
                    $firstName = $billing->getFirstName();
                    $lastName = $billing->getLastName();
                    $customer->setEmail($this->getEmail($order));
                    $customer->setFirstname(
                        (isset($firstName) and !empty($firstName))
                            ? $firstName : '.'
                    );
                    $customer->setLastname(
                        (isset($lastName) and !empty($lastName)) ?
                            $lastName : '.'
                    );
                    $customer->setPassword("cdiscountpassword");
                    $customer->save();
                } catch (\Exception $e) {
                    $this->logger->log(
                        'ERROR',
                        'Customer create failed. Order Id: #' .
                        $order . ' Message:' . $e->getMessage()
                    );
                    return false;
                }
            }
        }
        return $customer;
    }

    /**
     * @param $store
     * @param $customer
     * @param \Sdk\Order\Order $order
     * @param int $count
     * @return int
     * @throws \Exception
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function generateQuote(
        $store,
        $customer,
        $order = null,
        $count = 0
    ) {

        $shippingcost = 0;
        $cart_id = $this->cartManagementInterface->createEmptyCart();
        /** @var Quote $quote */
        $quote = $this->cartRepositoryInterface->get($cart_id);
        $quote->setStore($store);
        $quote->setCurrency();
        $customerId = $customer->getId();
        $customer = $this->customerRepository->getById($customerId);
        $quote->assignCustomer($customer);
        $itemAccepted = 0;

        $cdiscountCustomer = $order->getCustomer();
        $shipping = $order->getShippingAddress();
        $billing = $order->getBillingAddress();
        $items = $order->getOrderLineList()->getOrderLines();
        $reason = [];
//        print_r($order);
//        print_r($items);die;
        if (isset($items)) {
            $grandTotal = 0.0;
            $baseTotalSub = 0.0;
            foreach ($items as $item) {
                /** @var OrderLine $item */
                $sku = $item->getSellerProductId();
                if(isset($sku) && empty($sku)) {
                    continue;
                }
                if ($sku) {
                    $qty = $item->getQuantity();
                    $product = $this->product->create()->loadByAttribute('sku', $sku);
                    if ($product) {

                        $product = $this->product->create()->load($product->getEntityId());
                        if ($product->getStatus() == '1') {
                            /* Get stock item */
                            $stock = $this->stockRegistry
                                ->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
                            $stockStatus = ($stock->getQty() > 0) ? ($stock->getIsInStock() == '1' ?
                                ($stock->getQty() >= $qty ? true : false)
                                : false) : false;
                            if ($stockStatus) {

                                $itemAccepted++;
                                $price = $item->getPurchasePrice();
                                $shippingAmount = ($order->getShippedTotalShippingCharges() > 0)
                                    ? $order->getShippedTotalShippingCharges()
                                    : $order->getValidatedTotalShippingCharges();

                                $taxClassId = $product->getTaxClassId();
                                //$shippingcost = $item->getUnitShippingCharges();
                                $shippingcost = !empty($order->getShippedTotalShippingCharges())
                                    ? $order->getShippedTotalShippingCharges() : $order->getValidatedTotalShippingCharges();

                                $baseprice = $price / $qty;
                                $baseTotalSub += $baseprice;
                                $grandTotal += ($baseprice + $shippingcost);
                                /*if ($taxClassId) {
                                    $taxrate = $this->taxCalculation->getCalculatedRate(
                                        $taxClassId,
                                        $customerId,
                                        $store->getId()
                                    );
                                    $baseprice = $baseprice * 100 / (100 + $taxrate);
                                    $shippingcost = $shippingcost * 100 / (100 + $taxrate);
                                    $baseprice = number_format($baseprice, 2);
                                    $shippingcost = number_format($shippingcost, 2);
                                }*/


                                if ($this->registry->registry(\Ced\Cdiscount\Model\Carrier\Cdiscount::SHIPMENT_COST)) {
                                    $this->registry->unregister(\Ced\Cdiscount\Model\Carrier\Cdiscount::SHIPMENT_COST);
                                }

                                $this->registry->register(
                                    \Ced\Cdiscount\Model\Carrier\Cdiscount::SHIPMENT_COST,
                                    $shippingcost
                                );

                                $rowTotal = $price;
                                $product->setPrice($baseprice)
                                    ->setSpecialPrice($baseprice)
                                    ->setBasePrice($baseprice)
                                    ->setTierPrice([])
                                    ->setOriginalCustomPrice($baseprice)
                                    ->setRowTotal($rowTotal)
                                    ->setBaseRowTotal($rowTotal);
                                $product->setData("salable", true);
                                $quote->addProduct($product, (int)$qty);


//                                $itemAccepted++;
//                                $price = $item->getPurchasePrice();
//                                $shippingAmount = ($order->getShippedTotalShippingCharges() > 0)
//                                    ? $order->getShippedTotalShippingCharges()
//                                    : $order->getValidatedTotalShippingCharges();
//
//                                $taxClassId = $product->getTaxClassId();
//                                $shippingcost += $item->getUnitShippingCharges();
//
//
//                                $baseprice = $price/$qty;
//
//                                if ($taxClassId) {
//                                    $taxrate = $this->taxCalculation->getCalculatedRate(
//                                        $taxClassId,
//                                        $customerId,
//                                        $store->getId()
//                                    );
//
//                                    $baseprice = $baseprice * 100 / (100 + $taxrate);
//                                    $shippingcost = $shippingcost * 100 / (100 + $taxrate);
//                                    $baseprice = number_format($baseprice, 2);
//                                    $shippingcost = number_format($shippingcost, 2);
//                                }
//
//                                if ($this->registry->registry(\Ced\Cdiscount\Model\Carrier\Cdiscount::SHIPMENT_COST)) {
//                                    $this->registry->unregister(\Ced\Cdiscount\Model\Carrier\Cdiscount::SHIPMENT_COST);
//                                }
//
//                                $this->registry->register(
//                                    \Ced\Cdiscount\Model\Carrier\Cdiscount::SHIPMENT_COST,
//                                    $shippingcost
//                                );
//
//                                $rowTotal = $price ;
//                                $product->setPrice($baseprice)
//                                    ->setBasePrice($baseprice)
//                                    ->setOriginalCustomPrice($baseprice)
//                                    ->setRowTotal($rowTotal)
//                                    ->setBaseRowTotal($rowTotal);
//                                $product->setData("salable",true);
//                                $quote->addProduct($product, (int)$qty);
                            } else {
                                $reason[] = $sku . "SKU out of stock";
                            }
                        } else {
                            $reason[] = $sku . " SKU not enabled on store";
                        }
                    } else {
                        $reason[] = $sku . "  SKU not exist on store";
                    }
                } else {
                    $reason[] = "SKU not exist in order item";
                }
            }
            if ($itemAccepted == 0 && (isset($reason) && !empty($reason))) {
                $this->rejectOrder($order, $items, $reason);
            }

            if ($itemAccepted > 0) {
                try {
//                    $phoneNumber = $cdiscountCustomer->getPhone();
//                    if (is_array(!$phoneNumber) || !$phoneNumber) {
//                        $phoneNumber = $cdiscountCustomer->getMobilePhone();
//                    }
//                    $shipAddress = [
//                        'firstname' => $shipping->getFirstName(),
//                        'lastname' => $shipping->getLastName(),
//                        'street' => $shipping->getStreet(),
//                        'city' => $shipping->getCity(),
//                        'country' => $shipping->getCountry(),
//                        'country_id' => $shipping->getCountry(),
//                        'postcode' => $shipping->getZipCode(),
//                        'telephone' => (!is_array($phoneNumber) && !empty($phoneNumber))?$phoneNumber:12345,
//                        'fax' => '',
//                        'save_in_address_book' => 1
//                    ];
//
//                    $billAddress = [
//                        'firstname' => $billing->getFirstName(),
//                        'lastname' => $billing->getLastName(),
//                        'street' => $billing->getStreet(),
//                        'city' => $billing->getCity(),
//                        'country' => $billing->getCountry(),
//                        'country_id' => $billing->getCountry(),
//                        'postcode' => $billing->getZipCode(),
//                        'telephone' => (!is_array($phoneNumber) && !empty($phoneNumber))?$phoneNumber:12345,
//                        'fax' => '',
//                        'save_in_address_book' => 1
//                    ];

                    if ($cdiscountCustomer->getMobilePhone()) {
                        $phoneNumber = $cdiscountCustomer->getMobilePhone();
                    } else {
                        $phoneNumber = $cdiscountCustomer->getPhone();
                    }
                    $shippingFullAddress = $shipping->getStreet();
                    if (!empty($shipping->getBuilding())) {
                        $shippingFullAddress .= " , Bâtiment {$shipping->getBuilding()}";
                    }

                    if (!empty($shipping->getApartmentNumber())) {
                        $shippingFullAddress .= " , Appartement {$shipping->getApartmentNumber()}";
                    }

                    if (!empty($shipping->getPlaceName())) {
                        $shippingFullAddress .= " , Endroit {$shipping->getPlaceName()}";
                    }

                    $shipingName = $shipping->getFirstName();
                    $shippingLastName = $shipping->getLastName();
                    if (empty($shipingName) || empty($shippingLastName)) {
                        $shipingName = $billing->getFirstName();
                        $shippingLastName = $billing->getLastName();
                    }
                    $shipAddress = [
                        'firstname' => $shipingName,
                        'lastname' => $shippingLastName,
                        'company' => $shipping->getCompanyName(),
                        'street' => $shippingFullAddress,
                        'city' => $shipping->getCity(),
                        'country' => $shipping->getCountry(),
                        'country_id' => $shipping->getCountry(),
                        'postcode' => $shipping->getZipCode(),
                        'telephone' => (!is_array($phoneNumber) && !empty($phoneNumber)) ? $phoneNumber : 12345,
                        'fax' => '',
                        'save_in_address_book' => 1
                    ];


                    $billingFullAddress = $billing->getStreet();
                    if (!empty($billing->getBuilding())) {
                        $billingFullAddress .= " , Bâtiment {$billing->getBuilding()}";
                    }

                    if (!empty($billing->getApartmentNumber())) {
                        $billingFullAddress .= " , Appartement {$billing->getApartmentNumber()}";
                    }

                    if (!empty($billing->getPlaceName())) {
                        $billingFullAddress .= " , Endroit {$billing->getPlaceName()}";
                    }

                    $billAddress = [
                        'firstname' => $billing->getFirstName(),
                        'lastname' => $billing->getLastName(),
                        'company' => $billing->getCompanyName(),
                        'street' => $billingFullAddress,
                        'city' => $billing->getCity(),
                        'country' => $billing->getCountry(),
                        'country_id' => $billing->getCountry(),
                        'postcode' => $billing->getZipCode(),
                        'telephone' => (!is_array($phoneNumber) && !empty($phoneNumber)) ? $phoneNumber : 12345,
                        'fax' => '',
                        'save_in_address_book' => 1
                    ];

//                    print_r($shipAddress);
//                    print_r($billAddress);die('pl');
                    $shippingMethod = 'shipbycdiscount_shipbycdiscount';
                    if ($order->getShippingCode() == 'REL')  {
                        $shippingMethod = 'mondialrelay_pickup';
                    }
                    $quote->getBillingAddress()->addData($billAddress);
                    $shippingAddress = $quote->getShippingAddress()->addData($shipAddress);
                    $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
                        ->setShippingMethod($shippingMethod);
                    $quote->setPaymentMethod('paybycdiscount');
                    $quote->setInventoryProcessed(false);
                    $quote->save();
                    $quote->getPayment()->importData(
                        [
                            'method' => 'paybycdiscount'
                        ]
                    );
                    $quote->collectTotals()->save();
                    foreach ($quote->getAllItems() as $item) {
                        $item->setDiscountAmount(0);
                        $item->setBaseDiscountAmount(0);
                        $item->setOriginalCustomPrice($item->getPrice())
                            ->setOriginalPrice($item->getPrice())->save();
                    }
                    $magentoOrder = $this->cartManagementInterface->submit($quote);


                    $magentoOrder
                        ->setIncrementId($this->config->getOrderIdPrefix() . $magentoOrder->getIncrementId())
                        ->setSubtotalInclTax($baseTotalSub)
                        ->setGrandTotal($grandTotal)
                        ->setBaseGrandTotal($grandTotal)
                       // ->setShippingDescription($shipDescription)
                        ->save();
                    $count = isset($magentoOrder) ? $count + 1 : $count;
                    foreach ($magentoOrder->getAllItems() as $item) {
                        $item->setOriginalPrice($item->getPrice())
                            ->setBaseOriginalPrice($item->getPrice())
                            ->save();
                    }
                    // after save order
                    $orderPlace = substr($order->getCreationDate(), 0, 10);
                    /*$itemArray = [];
                    foreach ($items as $item) {
                        $itemArray = json_encode((array)$item);
                    }*/
                    $orderData = [
                        'cdiscount_order_id' => $order->getOrderNumber(),
                        'order_place_date' => $orderPlace,
                        'magento_order_id' => $magentoOrder->getId(),
                        'increment_id' => $magentoOrder->getIncrementId(),
                        'status' => \Ced\Cdiscount\Model\Source\Order\Status::IMPORTED,
                        'order_data' => serialize($order),
                        'order_items' => serialize($items)
                    ];
                    $this->orders->create()->addData($orderData)->save();
                    //$this->sendMail($order->getOrderNumber(), $magentoOrder->getIncrementId(), $orderPlace);
                    $this->generateInvoice($magentoOrder);
                    if($shippingMethod == 'mondialrelay_pickup') {
                        $morder = $this->objectManager->create(\Magento\Sales\Model\Order::class)->load($magentoOrder->getId());
                        $shippingAddressObj = $morder->getShippingAddress();
                        $shippingAddressObj->setMondialrelayCode('24R')
                            ->setMondialrelayPickupId($shipping->getRelayId())
                            ->save();
                    }
                } catch (\Exception $exception) {
                    //print_r($exception->getMessage());die('test');
                    $this->logger->log('ERROR', $exception->getMessage(), [
                        'path' => __METHOD__
                    ]);
                }
            }
        }
        return $count;
    }

    public function acknowledgeOrders($order)
    {
        $response = false;
        $purchaseId = $order->getorderNumber();
        $ordersData = $this->orders->create()->getCollection()
            ->addFieldToFilter('cdiscount_order_id',$purchaseId)
            ->getData();
        if (isset($ordersData) and !empty($ordersData)) {
            $orderItems = (unserialize($ordersData[0]['order_items']));
            $username = $this->config->getUserName();
            $password = $this->config->getUserPassword();
            $apiClient = $this->cdiscount->create(
                [
                    'username' => $username,
                    'password' => $password
                ]
            );
            $token = $apiClient->init();
            if (empty($token)) {
                return false;
            }
            $ordersPoint = $apiClient->getOrderPoint();
            $validate = $this->orderValidationFactory->create(
                [
                    'orderNumber' => $purchaseId
                ]
            );
            $validate->setOrderState(\Sdk\Order\OrderStateEnum::AcceptedBySeller);
            $orderLineList = $this->orderLineList;
            foreach ($orderItems as $orderItem) {
                $validateOrderLine = $this->validateOrderLineFactory->create(
                    [
                        'sellerProductId' => $orderItem->getSellerProductId(),
                        'acceptationState' => \Sdk\Order\OrderStateEnum::AcceptedBySeller,
                        'productCondition' => \Sdk\Order\ProductConditionEnum::NewS
                    ]
                );
                $orderLineList->addOrderLine($validateOrderLine);
                $validate->setOrderLineList($orderLineList);
            }
            $orderList = $this->orderList;
            $orderList->addOrder($validate);
            $validateOrderListResponse = $ordersPoint->validateOrderList($orderList);
            if (!$validateOrderListResponse->hasError()) {
                $response = true;
            }else {
                $response = true;
            }
            return $response;
        }
        return true;
    }

    /**
     * @param $order
     * @param array $items
     * @param array $reason
     * @return bool
     * @throws \Exception
     */
    public function rejectOrder($order, $items = [], $reason = [])
    {

        $orderFailed = $this->orderFailed->create()->load($order->getOrderNumber(), 'cdiscount_order_id');
        $addData = [
            'cdiscount_order_id' => $order->getOrderNumber(),
            'status' => \Ced\Cdiscount\Model\Source\Order\Status::CANCELLED,
            'reason' => $this->json->jsonEncode($reason),
            'order_date' => substr($order->getCreationDate(), 0, 10),
            'order_data' => serialize($order),
            'order_items' => serialize($items),
            //            'cancellations' => $this->json->jsonEncode($response),
        ];
        $orderFailed->addData($addData)->save($this->orderFailed);
        $this->logger->log(
            'ERROR',
            'Order failed request sends to Cdiscount. Order Id: #' . $order->getOrderNumber()
        );
        return true;
    }

    public function getShipmentProviders()
    {
        $providers = [];
        foreach (self::SHIPMENT_PROVIDERS as $SHIPMENT_PROVIDER) {
            $providers[] = $SHIPMENT_PROVIDER;
        }
        return $providers;
    }

    public function getCancelReasons($type = 'canceled')
    {
        $reasons = [];
        /*$cdiscountOrder = $this->objectManager->create(
            '\Cdiscount\Sdk\Order',
            ['config' => $this->config->getApiConfig()]
        );
        $cdiscountReasons = $cdiscountOrder->getCancelReasons();
        if (isset($cdiscountReasons['Reasons'])) {
            foreach ($cdiscountReasons['Reasons'] as $cdiscountReason) {
                if (isset($cdiscountReason['Type']) and $cdiscountReason['Type'] == $type) {
                    $reasons[] = $cdiscountReason;
                }
            }
        }*/
        return $reasons;
    }

    /**
     * @param $cdiscountOrderId
     * @param $mageOrderId
     * @param $placeDate
     * @return bool
     */
    public function sendMail($cdiscountOrderId, $mageOrderId, $placeDate)
    {
        $body = '<table cellpadding="0" cellspacing="0" border="0">
            <tr> <td> <table cellpadding="0" cellspacing="0" border="0">
                <tr> <td class="email-heading">
                    <h1>You have a new order from Cdiscount.</h1>
                    <p> Please review your admin panel."</p>
                </td> </tr>
            </table> </td> </tr>
            <tr> 
                <td>
                    <h4>Merchant Order Id' . $cdiscountOrderId . '</h4>
                </td>
                <td>
                    <h4>Magneto Order Id' . $mageOrderId . '</h4>
                </td>
                <td>
                    <h4>Order Place Date' . $placeDate . '</h4>
                </td>
            </tr>  
        </table>';
        $to_email = $this->scopeConfig->getValue('cdiscount_config/cdiscount_order/order_notify_email');
        $subject = 'Imp: New Cdiscount Order Imported';
        $senderEmail = 'cdiscountadmin@cedcommerce.com';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: ' . $senderEmail . '' . "\r\n";
//        mail($to_email, $subject, $body, $headers);
        return true;
    }

    /**
     * @param $order
     */
    public function generateInvoice($order)
    {
        try {
            $invoice = $this->objectManager->create('Magento\Sales\Model\Service\InvoiceService')
                ->prepareInvoice($order);
            $invoice->register()->setMpRewardEarn(0);
            $invoice->save();
            $transactionSave = $this->objectManager->create('Magento\Framework\DB\Transaction')
                ->addObject($invoice)->addObject($invoice->getOrder());
            $transactionSave->save();
            $order->addStatusHistoryComment(__('Notified customer about invoice #%1.', $invoice->getId()))
                ->setIsCustomerNotified(true)->save();
            $order->setStatus('processing')->save();
        } catch (\Exception $exception) {
            $this->logger->error(
                $exception->getMessage(), [
                    'method' => __METHOD__,
                    'trace' => $exception->getTraceAsString()
                ]
            );
        }
    }

    /**
     * Ship Cdiscount Order
     *
     * @param  array $data
     * @return array
     */
    public function shipOrder(array $data = [], $fromObserver = false)
    {
        $response = [
            'success' => false,
            'message' => []
        ];
        $response['message']['Type'] = 'order_shipment';
        $status = '';

        try {
            $username = $this->config->getUserName();
            $password = $this->config->getUserPassword();
            $apiClient = $this->cdiscount->create(
                [
                    'username' => $username,
                    'password' => $password
                ]
            );
            $token = $apiClient->init();
            if (empty($token)) {
                throw new \Exception('Token Invalid or not found');
            }
            if (isset($data['CdiscountOrderID']) and empty($data['CdiscountOrderID'])) {
                throw new \Exception('Error Occured Cdisount order id not Found');
            }
            $this->ids = $data['CdiscountOrderID'];
            $orderPoint = $apiClient->getOrderPoint();
            $orders = $this->orderValidationFactory->create(
                [
                    'orderNumber' => $data['CdiscountOrderID']
                ]
            );

            if (isset($data['ShippingProvider']) && empty($data['ShippingProvider'])) {
                throw new \Exception('ShippingProvider are missing.');
            }
            if (isset($data['TrackingNumber']) && empty($data['TrackingNumber'])) {
                throw new \Exception('TrackingNumber are missing.');
            }
            if (isset($data['TrackingUrl']) && !empty($data['TrackingUrl'])) {
                $trackingUrl = $data['TrackingUrl'];
                $orders->setTrackingUrl($trackingUrl);
            }
            $orders->setOrderState(\Sdk\Order\OrderStateEnum::Shipped);
            $orders->setCarrierName($data['ShippingProvider']);
            $orders->setTrackingNumber($data['TrackingNumber']);
            $orderList = $this->orderList;
            $orderLineList = $this->orderLineList;
            $magentoOrder = $this->objectManager
                ->create('\Magento\Sales\Model\Order')->load($data['OrderId']);
            $shipQty = [];
            $cdsicountOrderData = $this->orders->create()
                ->load($data['CdiscountOrderID'], 'cdiscount_order_id');
            $cdiscountOrder = @unserialize($cdsicountOrderData->getData('order_items'));
            if ($cdiscountOrder) {
                foreach ($cdiscountOrder as $val) {
                    if (!is_string($val->getSellerProductId())) {continue;}
                    $validateOrderLineList = $this->validateOrderLineFactory->create(
                        [
                            'sellerProductId' => $val->getSellerProductId(),
                            'acceptationState' => \Sdk\Order\OrderStateEnum::ShippedBySeller,
                            'productCondition' => $val->getProductCondition()
                        ]
                    );
                    $orderLineList->addOrderLine($validateOrderLineList);
                    $orders->setOrderLineList($orderLineList);
                }
                $orderList->addOrder($orders);
                $validateOrderListResponse = $orderPoint->validateOrderList($orderList);
                $status = $validateOrderListResponse;
                $this->logger->error('SHIPMENT', ['path' => __METHOD__,
                    'Response' => json_encode($status)]);
            } else {
                throw new \Exception('Order Data are missing.');
            }
            if ($status->hasError()) {
                $response['message']['Status'] = \Ced\Cdiscount\Model\Source\Feed\Status::FAILURE;
                $response['message']['Error'] = $status->getErrorMessage();
                $validateOrderResult = $status->getValidateOrderResults();
                if (isset($validateOrderResult)) {
                    foreach ($validateOrderResult->getValidateOrderResultList() as $validateOrder) {
                        $response['message']['OrderNumber'] = $validateOrder->getOrderNumber();
                        if (isset($validateOrder)) {
                            foreach ($validateOrder->getValidateOrderLineResults()->getValidateOrderLineResultList() as $validateOrderLineResult) {
                                $response['message']['SellerProductID'] = $validateOrderLineResult->getSellerProductId();
                                $response['message']['ProductUpdated'] = $validateOrderLineResult->isUpdated();
                            }
                        }
                    }
                }
                $this->saveResponse($response);
            } elseif (!$status->hasError()) {
                if ($fromObserver == false) {
                    $this->generateShipment($magentoOrder, $shipQty);
                }
                $response['message']['Status'] = \Ced\Cdiscount\Model\Source\Feed\Status::SUCCESS;
                $response['message']['Error'] = '{}';
                $validateOrderResult = $status->getValidateOrderResults();
                if (isset($validateOrderResult)) {
                    foreach ($validateOrderResult->getValidateOrderResultList() as $validateOrder) {
                        $response['message']['OrderNumber'] = $validateOrder->getOrderNumber();
                        if (isset($validateOrder)) {
                            foreach ($validateOrder->getValidateOrderLineResults()->getValidateOrderLineResultList()
                                     as $validateOrderLineResult) {
                                $response['message']['SellerProductID'] = $validateOrderLineResult->getSellerProductId();
                                $response['message']['ProductUpdated'] = $validateOrderLineResult->isUpdated();
                            }
                        }
                    }
                }
                $this->saveResponse($response);
                // Saving fulfillment data.
                $cdiscountOrder = $this->orders->create()->load($data['OrderId'], 'magento_order_id');
                $data['Response'] = $response['message'];

                $shipments = [];
                if (!empty($cdiscountOrder->getData('shipments'))) {
                    $shipments = $this->json->jsonDecode($cdiscountOrder->getData('shipments'));
                }
                $shipments[] = $data;

                $cdiscountOrder->setData('shipments', $this->json->jsonEncode($shipments));
                $cdiscountOrder->setData('status', \Ced\Cdiscount\Model\Source\Order\Status::SHIPPED);
                $cdiscountOrder->save();
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), ['path' => __METHOD__,
                'trace' => $exception->getTraceAsString()]);
            $response['message'] = $exception->getMessage();
        }
        return $response;
    }

    /**
     * Cancel Cdiscount Order
     *
     * @param  array $data
     * @return array
     */
    public function cancelOrder(array $data = [])
    {
        $response = [
            'success' => false,
            'message' => []
        ];
        $response['message']['Type'] = 'order_cancellation';

        try {
            $magentoOrder = $this->objectManager
                ->create('\Magento\Sales\Model\Order')->load($data['OrderId']);
            $cancel = [];

            if (isset($data['CdiscountOrderId']) and !empty($data['CdiscountOrderId'])) {
                $username = $this->config->getUserName();
                $password = $this->config->getUserPassword();
                $apiClient = $this->cdiscount->create(
                    [
                        'username' => $username,
                        'password' => $password
                    ]
                );
                $token = $apiClient->init();
                if (empty($token)) {
                    throw new \Exception('Token Invalid or not found');
                }
                $this->ids = $data['CdiscountOrderId'];
                $orderPoint = $apiClient->getOrderPoint();
                $orders = $this->orderValidationFactory->create(
                    [
                        'orderNumber' => $data['CdiscountOrderId']
                    ]
                );
                $orders->setOrderState(\Sdk\Order\OrderStateEnum::ShipmentRefusedBySeller);
                $orderList = $this->orderList;
                $orderLineList = $this->orderLineList;

                $validateOrderLineList = $this->validateOrderLineFactory->create(
                    [
                        'sellerProductId' => $data['SKU'],
                        'acceptationState' => \Sdk\Order\OrderStateEnum::ShipmentRefusedBySeller,
                        'productCondition' => \Sdk\Order\ProductConditionEnum::VeryGoodState
                    ]
                );
                $orderLineList->addOrderLine($validateOrderLineList);
                $orders->setOrderLineList($orderLineList);

                $orderList->addOrder($orders);
                $validateOrderListResponse = $orderPoint->validateOrderList($orderList);
                $status = $validateOrderListResponse;

                //foreach ($data['CdiscountOrderId'] as $orderItemId) {
                $orderItemId = $data['CdiscountOrderId'];
                // Preparing cancel qty for magento credit memo
                $cancelQty = $data['Quantity'];

                if ($status->hasError()) {
                    $response['message']['Status'] = \Ced\Cdiscount\Model\Source\Feed\Status::FAILURE;
                    $response['message']['Error'] = $status->getErrorMessage();
                    $validateOrderResult = $status->getValidateOrderResults();
                    if (isset($validateOrderResult)) {
                        foreach ($validateOrderResult->getValidateOrderResultList() as $validateOrder) {
                            $response['message']['OrderNumber'] = $validateOrder->getOrderNumber();
                            if (isset($validateOrder)) {
                                foreach ($validateOrder->getValidateOrderLineResults()->getValidateOrderLineResultList() as $validateOrderLineResult) {
                                    $response['message']['SellerProductID'] = $validateOrderLineResult->getSellerProductId();
                                    $response['message']['ProductUpdated'] = $validateOrderLineResult->isUpdated();
                                }
                            }
                        }
                    }
                    $this->saveResponse($response);
                } elseif (!$status->hasError()) {
                    $this->generateCreditMemo($magentoOrder, $cancelQty);
                    $response['message'][] = $orderItemId . ' Cancelled successfully. ';
                    $response['success'] = true;
                    // Saving fulfillment data.
                    $cdiscountOrder = $this->orders->create()->load($data['OrderId'], 'magento_order_id');

                    $data['Status'] = \Ced\Cdiscount\Model\Source\Feed\Status::SUCCESS;
                    $data['Response'] = $response['message'];

                    $cancellations = [];
                    if (!empty($cdiscountOrder->getData('cancellations'))) {
                        $cancellations = $this->json->jsonDecode($cdiscountOrder->getData('cancellations'));
                    }
                    $cancellations[] = $data;

                    $cdiscountOrder->setData('cancellations', $this->json->jsonEncode($cancellations));
                    $cdiscountOrder->setData('status', \Ced\Cdiscount\Model\Source\Order\Status::SHIPPED);
                    $cdiscountOrder->save();
                } else {
                    $response['message'][] = $orderItemId . " " . $status['Error'];
                }
                //}
            } else {
                throw new \Exception('OrderItemIds are missing.');
            }
        } catch (\Exception $exception) {
            $response['message'] = $exception->getMessage();
        }
        return $response;
    }

    /**
     * @param $count
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function notificationSuccess($count)
    {
        $model = $this->inbox;
        $date = date("Y-m-d H:i:s");
        $model->setData('severity', 4);
        $model->setData('date_added', $date);
        $model->setData('title', "New Cdiscount Orders");
        $model->setData('description', "Congratulation! You have received " . $count . " new orders form Cdiscount");
        $model->setData('url', "#");
        $model->setData('is_read', 0);
        $model->setData('is_remove', 0);
        $model->getResource()->save($model);
    }

    /**
     * @param $order
     * @param $cancelleditems
     */
    public function generateShipment($order, $cancelleditems)
    {
        $shipment = $this->prepareShipment($order, $cancelleditems);
        if ($shipment) {
            $shipment->register();
            $shipment->getOrder()->setIsInProcess(true);
            try {
                $transactionSave = $this->objectManager->create('Magento\Framework\DB\Transaction')
                    ->addObject($shipment)->addObject($shipment->getOrder());
                $transactionSave->save();
                $order->setStatus('complete')->save();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage('Error in saving shipping:' . $e->getMessage());
            }
        }
    }

    /**
     * @param $order
     * @param $cancelleditems
     * @return bool
     */
    public function prepareShipment($order, $cancelleditems)
    {
        $shipment = $this->objectManager->get('Magento\Sales\Model\Order\ShipmentFactory')
            ->create($order, isset($cancelleditems) ? $cancelleditems : [], []);
        if (!$shipment->getTotalQty()) {
            return false;
        }
        return $shipment;
    }

    /**
     * @param $order
     * @param $cancelleditems
     */

    public function generateCreditMemo($order, $cancelleditems)
    {
        foreach ($order->getAllItems() as $orderItems) {
            $items_id = $orderItems->getId();
            $order_id = $orderItems->getOrderId();
        }
        $creditmemoLoader = $this->creditmemoLoaderFactory->create();
        $creditmemoLoader->setOrderId($order_id);
        foreach ($cancelleditems as $item_id => $cancelQty) {
            $creditmemo[$item_id] = ['qty' => $cancelQty];
        }
        $items = [
            'items' => $creditmemo,
            'do_offline' => '1',
            'comment_text' => 'Cdiscount Cancelled Orders',
            'adjustment_positive' => '0',
            'adjustment_negative' => '0'
        ];
        $creditmemoLoader->setCreditmemo($items);
        $creditmemo = $creditmemoLoader->load();
        $creditmemoManagement = $this->objectManager
            ->create('Magento\Sales\Api\CreditmemoManagementInterface');
        if ($creditmemo) {
            $creditmemo->setOfflineRequested(true);
            $creditmemoManagement->refund($creditmemo, true);
        }
    }

    /**
     * @param $message
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function notificationFailed($message)
    {
        $date = date("Y-m-d H:i:s");
        $model = $this->inbox;
        $model->setData('severity', 1);
        $model->setData('date_added', $date);
        $model->setData('title', "Failed Cdiscount Order");
        $model->setData('description', "You have one pending order." . $message);
        $model->setData('url', "#");
        $model->setData('is_read', 0);
        $model->setData('is_remove', 0);
        $model->getResource()->save($model);
    }

    public function processOrderItems($order)
    {
        $cancelledIds = array();
        $shippedIds = array();
        $items = array();
        $shipments = json_decode($order->getShipments(), true);

        $shipments = (isset($shipments) and is_array($shipments)) ? $shipments : array();
				
        /*echo '<pre>';
        print_r($shipments);die;*/
        //@todo shipment
        if (isset($shipments) && !empty($shipments)) {
            foreach ($shipments as $shipment) {
                if(isset($shipment['Response'][0]) && is_array($shipment['Response'][0])) 
				{
                    foreach($shipment['Response'] as $spid) 
					{
                        $shippedIds[] = $spid;
                    }
                } 
				else 
				{
					$shippedIds[] = $shipment['Response']['SellerProductID'];
                }
            }
        }

        $cancellations = json_decode($order->getCancellations(), true);

        //@todo cancelle
        $cancellations = (isset($cancellations) and is_array($cancellations)) ? $cancellations : [];


        if (isset($cancellations) && !empty($cancellations)) {
            foreach ($cancellations as $cancellation) {
                $cancelledIds[] = $cancellation['Response']['SellerProductID'];
            }
        }
        $cdiscountOrderItemsData = unserialize($order->getOrderItems()); // update
        foreach ($cdiscountOrderItemsData as $orderItem) {
            if (!in_array($orderItem->getsellerProductId(), $shippedIds) && !in_array($orderItem->getsellerProductId(), $cancelledIds)) {
                $items[] = $orderItem;
            }
        }

        return $items;
    }

    /**
     * Save Response to db
     *
     * @param  array $response
     * @return boolean
     */
    public function saveResponse($response = [])
    {
        //remove index if already set.
        $this->registry->unregister('cdiscount_product_errors');
        if (is_array($response)) {
            try {
                $this->registry->register(
                    'cdiscount_product_errors',
                    $response['message']
                );
                $feedModel = $this->feeds->create();
                $feedModel->addData(
                    [
                        'feed_id' => $this->dateTime->gmtTimestamp(),
                        'type' => $response['message']['Type'],
                        'feed_response' => $this->json->jsonEncode(
                            ['Body' => json_encode($response), 'Errors' => $response['message']['Error']]
                        ),
                        'status' => $response['message']['Status'],
                        'feed_file' => json_encode($response),
                        'response_file' => json_encode($response),
                        'feed_created_date' => $this->dateTime->date("Y-m-d"),
                        'feed_executed_date' => $this->dateTime->date("Y-m-d"),
                        'product_ids' => $this->json->jsonEncode($this->ids)
                    ]
                );
                $feedModel->save();
                return true;
            } catch (\Exception $e) {
                if ($this->config->debugMode) {
                    $this->logger->debug($e->getMessage());
                }
            }
        }
        return false;
    }
}
