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
 * @package   Ced_RueDuCommerce
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RueDuCommerce\Helper;

/**
 * Class Order
 *
 * @package Ced\RueDuCommerce\Helper
 */
class Order extends \Magento\Framework\App\Helper\AbstractHelper
{

    const DEFAULT_EMAIL = 'customer@rueducommerce.com';

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
     * @var \Ced\RueDuCommerce\Model\Orders
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
     * @var \Ced\RueDuCommerce\Model\OrderFailed
     */
    public $orderFailed;

    /**
     * @var \RueDuCommerceSdk\Order
     */
    public $rueducommerce;

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
     * @var \Ced\RueDuCommerce\Model\FeedsFactory
     */
    public $feeds;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $dateTime;

    /**
     * @var \Magento\Sales\Model\Order\AddressRepository
     */
    public $repositoryAddress;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    public $salesOrder;

    /**
     * Order constructor.
     *
     * @param \Magento\Framework\App\Helper\Context                             $context
     * @param \Magento\Framework\objectManagerInterface                         $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                       $dateTime
     * @param \Magento\Store\Model\StoreManagerInterface                        $storeManager
     * @param \Magento\Customer\Model\CustomerFactory                           $customerFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface                 $customerRepository
     * @param \Magento\Catalog\Model\ProductRepository                          $productRepository
     * @param \Magento\Catalog\Model\ProductFactory                             $product
     * @param \Magento\Framework\Json\Helper\Data                               $json
     * @param \Magento\Framework\Registry                                       $registry
     * @param \Magento\Sales\Model\Service\OrderService                         $orderService
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface                        $cartRepositoryInterface
     * @param \Magento\Quote\Api\CartManagementInterface                        $cartManagementInterface
     * @param \Magento\Framework\App\Cache\TypeListInterface                    $cache
     * @param \Magento\AdminNotification\Model\Inbox                            $inbox
     * @param \Magento\Framework\Message\ManagerInterface                       $manager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface              $stockRegistry
     * @param \Ced\RueDuCommerce\Model\OrdersFactory                                   $orders
     * @param \Ced\RueDuCommerce\Model\FeedsFactory                                    $feedsFactory
     * @param \Ced\RueDuCommerce\Model\OrderFailedFactory                              $orderFailed
     * @param Config                                                            $config
     * @param Logger                                                            $logger
     * @param \RueDuCommerceSdk\OrderFactory                                          $rueducommerce
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
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Framework\App\Cache\TypeListInterface $cache,
        \Magento\AdminNotification\Model\Inbox $inbox,
        \Magento\Framework\Message\ManagerInterface $manager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Ced\RueDuCommerce\Model\OrdersFactory $orders,
        \Ced\RueDuCommerce\Model\FeedsFactory $feedsFactory,
        \Ced\RueDuCommerce\Model\OrderFailedFactory $orderFailed,
        \Ced\RueDuCommerce\Helper\Config $config,
        \Ced\RueDuCommerce\Helper\Logger $logger,
        \RueDuCommerceSdk\OrderFactory $rueducommerce,
        \Magento\Sales\Model\Order\AddressRepository $repositoryAddress,
        \Magento\Sales\Api\Data\OrderInterface $salesOrderApi
    ) {
        parent::__construct($context);
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
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

        $this->orderFailed = $orderFailed;
        $this->feeds = $feedsFactory;
        $this->rueducommerce = $rueducommerce;
        $this->logger = $logger;
        $this->config = $config;
        $this->repositoryAddress= $repositoryAddress;
        $this->salesOrder = $salesOrderApi;
    }

    /**
     * @return bool
     */
    public function importOrders()
    {
        try {
            $storeId = $this->config->getStore();
            $store = $this->storeManager->getStore($storeId);
            $websiteId = $store->getWebsiteId();

            $orderList = $this->rueducommerce->create(
                [
                    'config' => $this->config->getApiConfig('order'),
                ]
            );

            $response = $orderList->getOrders();
            //echo"<pre>";print_r($response);die();
            $count = 0;
            if (isset($response['orders']['_value']) && is_array($response['orders']['_value'])) {
                foreach ($response['orders']['_value']['order'] as $order) {
                    $rueducommerceOrderId = $order['_value']['infocommande']['refid'];
                    $rueducommerceOrder = $this->orders->create()
                        ->getCollection()
                        ->addFieldToFilter('rueducommerce_order_id', $rueducommerceOrderId);
                    if (!$this->validateString($rueducommerceOrder->getData())) {
                        $customer = $this->getCustomer($order, $websiteId);
                        if ($customer !== false) {
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

            return false;
        } catch (\Exception $e) {
            $this->logger->error('Import Order', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
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
        $customerId = $this->config->getDefaultCustomer();
        if ($customerId === false && isset($order['_value']['utilisateur'][0]) && $order['_value']['utilisateur'][0]['_value']['email']){
            $customerCustomEmail = $order['_value']['utilisateur'][0]['_value']['email'];
            return $customerCustomEmail;
        } else {
            return $customerId;
        }
    }

    /**
     * @param $order
     * @param $websiteId
     * @return bool|\Magento\Customer\Model\Customer
     */
    public function getCustomer($order, $websiteId)
    {
        try {
            /*$customerId = $this->config->getDefaultCustomer();
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
            } else {*/
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
                    $storeId = $this->config->getStore();
                    $store = $this->storeManager->getStore($storeId);
                    $customer->setStore($store);
                    $customer->setWebsiteId($websiteId);
                    $customer->setEmail($this->getEmail($order));
                    $customer->setFirstname(
                        (isset($order['_value']['utilisateur'][0]['_value']['nom']['_value']) and !empty($order['_value']['utilisateur'][0]['_value']['nom']['_value']))
                            ? $order['_value']['utilisateur'][0]['_value']['nom']['_value'] : '.'
                    );
                    $customer->setLastname(
                        (isset($order['_value']['utilisateur'][0]['_value']['prenom']) and !empty($order['_value']['utilisateur'][0]['_value']['prenom'])) ?
                            $order['_value']['utilisateur'][0]['_value']['prenom'] : '.'
                    );
                    $customer->setPassword("rueducommercepassword");
                    $customer->save();
                } catch (\Exception $e) {
                    $this->logger->log(
                        'ERROR',
                        'Customer create failed. Order Id: #' .
                        $order['_value']['infocommande']['refid'] . ' Message : ' . $e->getMessage()
                    );
                    return false;
                }
            }
            //}

            return $customer;
        } catch (\Exception $e) {
            $this->logger->error('Create Customer', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    /**
     * @param string   $store
     * @param $customer
     * @param array    $order
     * @param integer  $count
     * @return mixed
     */
    public function generateQuote(
        $store,
        $customer,
        $order = null,
        $count = 0
    ) {
        $shippingcost = 0;
        $cart_id = $this->cartManagementInterface->createEmptyCart();
        $quote = $this->cartRepositoryInterface->get($cart_id);
        $quote->setStore($store);
        $quote->setCurrency();
        $quote->setCustomerNoteNotify(false);
        $customer = $this->customerRepository->getById($customer->getId());
        $quote->assignCustomer($customer);
        $itemAccepted = 0;
        $subTotal = 0;
        $rejectItemsArray = $acceptItemsArray = [];
        try {
            $reason = [];

            if (isset($order['_value']['infocommande']['list']['_value']['produit'])) {
                $failedOrder = false;
                $shippingcost = isset($order['_value']['infocommande']['transport']['montant']['_value']) ? $order['_value']['infocommande']['transport']['montant']['_value'] : 0;
                if (!isset($order['_value']['infocommande']['list']['_value']['produit'][0])) {
                    $order['_value']['infocommande']['list']['_value']['produit'] = array(
                        0 => $order['_value']['infocommande']['list']['_value']['produit'],
                    );
                }
                foreach ($order['_value']['infocommande']['list']['_value']['produit'] as $item) {
                    if (isset($item['_attribute'])) {
                        //$lineNumber = $item['order_line_id'];
                        $productItem = $item['_attribute'];
                        $qty = $productItem['nb'];
                        $product = $this->product->create()->loadByAttribute('sku', $productItem['merchantProductId']);
                        if (isset($product) and !empty($product)) {
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
                                    $price = $productItem['price'];
                                    $baseprice = $qty * $price;
                                    /*$shippingcost += isset($item ['shipping_price']) ?
                                        $item ['shipping_price'] : 0;*/
                                    $rowTotal = $price * $qty;
                                    $subTotal += $rowTotal;
                                    $product->setPrice($price)
                                        ->setBasePrice($baseprice)
                                        ->setSpecialPrice($baseprice)
                                        ->setOriginalCustomPrice($price)
                                        ->setRowTotal($rowTotal)
                                        ->setBaseRowTotal($rowTotal);
                                    $quote->addProduct($product, (int)$qty);
                                    /*if($item['order_line_state'] == "WAITING_ACCEPTANCE") {
                                        $acceptItemsArray[] = [
                                            'order_line' => [
                                                '_attribute' => [],
                                                '_value' => [
                                                    'accepted' => "true",
                                                    'id' => $lineNumber
                                                ]
                                            ]
                                        ];
                                    }*/
                                } else {
                                    $reason[] = 'Product with sku - ' . $productItem['merchantProductId'] . " SKU out of stock";
                                    $failedOrder = true;
                                    /*if($item['order_line_state'] == "WAITING_ACCEPTANCE") {
                                        $rejectItemsArray[] = [
                                            'order_line' => [
                                                '_attribute' => [],
                                                '_value' => [
                                                    'accepted' => "false",
                                                    'id' => $lineNumber
                                                ]
                                            ]
                                        ];
                                    }*/
                                }
                            } else {
                                $reason[] = 'Product with sku - ' . $productItem['merchantProductId'] . " SKU not enabled on store";
                                $failedOrder = true;
                                /*if($item['order_line_state'] == "WAITING_ACCEPTANCE") {
                                    $rejectItemsArray[] = [
                                        'order_line' => [
                                            '_attribute' => [],
                                            '_value' => [
                                                'accepted' => "false",
                                                'id' => $lineNumber
                                            ]
                                        ]
                                    ];
                                }*/
                            }
                        } else {
                            $reason[] = 'Product with sku - ' . $productItem['merchantProductId'] . " not exist on store";
                            $failedOrder = true;
                            /*if($item['order_line_state'] == "WAITING_ACCEPTANCE") {
                                $rejectItemsArray[] = [
                                    'order_line' => [
                                        '_attribute' => [],
                                        '_value' => [
                                            'accepted' => "false",
                                            'id' => $lineNumber
                                        ]
                                    ]
                                ];
                            }*/
                        }
                    } else {
                        $reason[] = "SKU not exist in order item";
                        $failedOrder = true;
                        /*if($item['order_line_state'] == "WAITING_ACCEPTANCE") {
                            $rejectItemsArray[] = [
                                'order_line' => [
                                    '_attribute' => [],
                                    '_value' => [
                                        'accepted' => "false",
                                        'id' => $lineNumber
                                    ]
                                ]
                            ];
                        }*/
                    }
                }

                if ($failedOrder) {
                    $this->rejectOrder($order, $reason);
                } else if(!$failedOrder) {
                    $countryCode = 'FR';
                    $stateCode = '';
                    /*$stateModel = $this->objectManager->create('Magento\Directory\Model\RegionFactory')->create()
                        ->getCollection()->addFieldToFilter('country_id', $countryCode)->getFirstItem();
                    if(count($stateModel) > 0) {
                        $stateCode = $stateModel->getCode();
                    }*/
                    try {
                        if (isset($order['_value']['utilisateur'][0]['_value']) && is_array($order['_value']['utilisateur'][0]['_value']))
                            $billing = $order['_value']['utilisateur'][0]['_value'];

                        if (isset($order['_value']['utilisateur'][1]['_value']) && is_array($order['_value']['utilisateur'][1]['_value']))
                            $shipping = $order['_value']['utilisateur'][1]['_value'];


                        $shipping_address_street_2 = '';
                        if(isset($shipping['adresse']['_value']['rue2']) && !is_array($shipping['adresse']['_value']['rue2']))
                            $shipping_address_street_2 = $shipping['adresse']['_value']['rue2'];

                        $billing_address_street_2 = '';
                        if(isset($billing['adresse']['_value']['rue2']) && !is_array($billing['adresse']['_value']['rue2']))
                            $billing_address_street_2 = $billing['adresse']['_value']['rue2'];

                        $shipping_address_street_1 = '';
                        if(isset($shipping['adresse']['_value']['rue1']) && !is_array($shipping['adresse']['_value']['rue1']))
                            $shipping_address_street_1 = $shipping['adresse']['_value']['rue1'];

                        $billing_address_street_1 = '';
                        if(isset($billing['adresse']['_value']['rue1']) && !is_array($billing['adresse']['_value']['rue1']))
                            $billing_address_street_1 = $billing['adresse']['_value']['rue1'];

                        /*$shipping_address_company = '';
                        if(isset($order['customer']['shipping_address']['company']) && !is_array($order['customer']['shipping_address']['company']))
                            $shipping_address_company = $order['customer']['shipping_address']['company'];

                        $billing_address_company = '';
                        if(isset($order['customer']['billing_address']['company']) && !is_array($order['customer']['billing_address']['company']))
                            $billing_address_company=  $order['customer']['billing_address']['company'];*/

                        $shipAddress = [
                            'firstname' => (isset($shipping['nom']['_value']) and !empty($shipping['nom']['_value'])) ? ($shipping['nom']['_value']) : 'First',
                            'lastname' => (isset($shipping['prenom']) and !empty($shipping['prenom'])) ? $shipping['prenom'] : 'Last',
                            'street' => ($shipping_address_street_1) ? $shipping_address_street_1 . " " . $shipping_address_street_2 : 'N/A',
                            'city' => isset($shipping['adresse']['_value']['ville']) ? $shipping['adresse']['_value']['ville']: 'N/A' ,
                            'country' =>  isset($shipping['adresse']['_value']['pays']) ? $shipping['adresse']['_value']['pays'] : 'N/A',
                            'country_id' => $countryCode,
                            'region' => $stateCode,
                            'postcode' => isset($shipping['adresse']['_value']['cpostal'])?$shipping['adresse']['_value']['cpostal'] : 'N/A',
                            'telephone' => isset($shipping['telhome']) && !empty($shipping['telhome']) ?  $shipping['telhome'] : '+1123456789',
                            'fax' => isset($shipping['telfax']) && !empty($shipping['telfax']) ?  $shipping['telfax'] : '+12129043',
                            'save_in_address_book' => 1
                        ];

                        $billAddress = [
                            'firstname' => (isset($billing['nom']['_value']) and !empty($billing['nom']['_value'])) ? ($billing['nom']['_value']) : 'First',
                            'lastname' => (isset($billing['prenom']) and !empty($billing['prenom'])) ? $billing['prenom'] : 'Last',
                            'street' => ($billing_address_street_1) ? $billing_address_street_1 . " " . $billing_address_street_2 : 'N/A',
                            'city' => isset($billing['adresse']['_value']['ville']) ? $billing['adresse']['_value']['ville']: 'N/A' ,
                            'country' =>  isset($billing['adresse']['_value']['pays']) ? $billing['adresse']['_value']['pays'] : 'N/A',
                            'country_id' => $countryCode,
                            'region' => $stateCode,
                            'postcode' => isset($billing['adresse']['_value']['cpostal'])?$billing['adresse']['_value']['cpostal'] : 'N/A',
                            'telephone' => isset($billing['telhome']) && !empty($billing['telhome']) ?  $billing['telhome'] : '+1123456789',
                            'fax' => isset($billing['telfax']) && !empty($billing['telfax']) ?  $billing['telfax'] : '+12129043',
                            'save_in_address_book' => 1
                        ];
                        $quote->getBillingAddress()->addData($billAddress);
                        $shippingAddress = $quote->getShippingAddress()->addData($shipAddress);
                        $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
                            ->setShippingMethod('shipbyrueducommerce_shipbyrueducommerce');
                        $quote->setPaymentMethod('paybyrueducommerce');
                        $quote->setInventoryProcessed(false);
                        $quote->save();
                        $quote->getPayment()->importData(
                            [
                                'method' => 'paybyrueducommerce'
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
                        $magentoOrder->setShippingAmount($shippingcost)
                            ->setBaseShippingAmount($shippingcost)
                            ->setShippingInclTax($shippingcost)
                            ->setBaseShippingInclTax($shippingcost)
                            ->setGrandTotal($subTotal + $shippingcost)
                            ->setIncrementId($this->config->getOrderIdPrefix() . $magentoOrder->getIncrementId())
                            ->save();
                        $count = isset($magentoOrder) ? $count + 1 : $count;
                        foreach ($magentoOrder->getAllItems() as $item) {
                            $item->setOriginalPrice($item->getPrice())
                                ->setBaseOriginalPrice($item->getPrice())
                                ->save();
                        }
                        // after save order
                        $orderPlace = substr($order['_value']['infocommande']['date'], 0, 11);
                        $orderId = $order['_value']['infocommande']['refid'];
                        $orderMoId = $order['_attribute']['morid'];
                        $orderData = [
                            'rueducommerce_order_id' => $orderId,
                            'rueducommerce_order_moid' => $orderMoId,
                            'order_place_date' => $orderPlace,
                            'magento_order_id' => $magentoOrder->getId(),
                            'increment_id' => $magentoOrder->getIncrementId(),
                            'status' => $order['_value']['infocommande']['status'],
                            'order_data' => $this->json->jsonEncode($order),
                            'order_items' => $this->json->jsonEncode($order['_value']['infocommande']['list']['_value']['produit'])
                        ];
                        $this->orders->create()->addData($orderData)->save($this->orders);
                        // = $this->config->getAutoAcceptOrderSetting();
                        /*if($autoAccept) {
                            $this->autoOrderAccept($order['order_id'], $acceptItemsArray);
                            $this->generateInvoice($magentoOrder);
                        }*/
                        /*$autoCancellation = $this->config->getAutoCancelOrderSetting();
                        if($autoCancellation) {
                            $this->autoOrderAccept($order['order_id'], $rejectItemsArray);
                        }*/
                        $this->generateInvoice($magentoOrder);
                        $this->sendMail($order['_value']['infocommande']['refid'], $magentoOrder->getIncrementId(), $orderPlace);

                    } catch (\Exception $exception) {
                        $reason[] = $exception->getMessage();
                        $orderFailed = $this->orderFailed->create()->load($order['_value']['infocommande']['refid'], 'rueducommerce_order_id');
                        $orderMoId = $order['_attribute']['morid'];
                        $addData = [
                            'rueducommerce_order_id' => $order['_value']['infocommande']['refid'],
                            'rueducommerce_order_moid' => $orderMoId,
                            'status' => $order['_value']['infocommande']['status'],
                            'reason' => $this->json->jsonEncode($reason),
                            'order_place_date' => substr($order['_value']['infocommande']['date'], 0, 11),
                            'order_data' => $this->json->jsonEncode($order),
                            'order_items' => isset($order['_value']['infocommande']['list']['_value']['produit']) ? $this->json->jsonEncode($order['_value']['infocommande']['list']['_value']['produit']) : '',
                        ];

                        $orderFailed->addData($addData)->save($this->orderFailed);
                        $this->logger->error('Generate Quote', ['path' => __METHOD__, 'exception' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]);
                    }
                }
            }

            return $count;
        } catch (\Exception $e) {
            $this->logger->error('Generate Quote', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }
    /**
     * @param array $order
     * @param array $reason
     * @return bool
     */
    public function rejectOrder(array $order, array $reason = [])
    {
        try {
            $orderMoId = $order['_attribute']['morid'];
            $orderId = $order['_value']['infocommande']['refid'];
            $orderFailed = $this->orderFailed->create()->load($orderId, 'rueducommerce_order_id');
            $addData = [
                'rueducommerce_order_id' => $orderId,
                'rueducommerce_order_moid' => $orderMoId,
                'status' => $order['_value']['infocommande']['status'],
                'reason' => $this->json->jsonEncode($reason),
                'order_place_date' => substr($order['_value']['infocommande']['date'], 0, 11),
                'order_data' => $this->json->jsonEncode($order),
                'order_items' => isset($order['_value']['infocommande']['list']['_value']['produit']) ? $this->json->jsonEncode($order['_value']['infocommande']['list']['_value']['produit']) : '',
            ];

            $orderFailed->addData($addData)->save($this->orderFailed);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Reject Order', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    public function autoOrderAccept($RueDuCommerceOrderId, $acceptanceArray)
    {
        $acceptanceData = array(
            'order' => array(
                '_attribute' => array(),
                '_value' => array(
                    'order_lines' => array(
                        '_attribute' => array(),
                        '_value' => $acceptanceArray
                    )
                )
            )
        );
        $rueducommerceOrder = $this->objectManager->create(
            '\RueDuCommerceSdk\Order',
            ['config' => $this->config->getApiConfig()]
        );
        $response = $rueducommerceOrder->acceptrejectOrderLines($RueDuCommerceOrderId, $acceptanceData);
        $this->logger->info('Auto Accept Order Acceptance Data', ['path' => __METHOD__, 'AcceptanceData' => json_encode($acceptanceData)]);
        try {
            $rueducommerceOrder = $this->orders->create()
                ->getCollection()
                ->addFieldToFilter('rueducommerce_order_id', $RueDuCommerceOrderId)->getData();

            if (!empty($rueducommerceOrder)) {
                $id = $rueducommerceOrder [0] ['id'];
                $model = $this->orders->create()->load($id);
                $model->setStatus('WAITING_DEBIT');
                $model->save();
            }
        } catch (\Exception $e) {
            $this->logger->error('Auto Accept Order', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
        return $response;
    }
    public function getShipmentProviders()
    {
        $providers = [];
        $rueducommerceOrder = $this->objectManager->create(
            '\RueDuCommerceSdk\Order',
            ['config' => $this->config->getApiConfig()]
        );
        $rueducommerceProviders = $rueducommerceOrder->getShippingMethods();

        if (isset($rueducommerceProviders)) {
            $providers = $rueducommerceProviders;
        }
        return $providers;
    }

    public function getCancelReasons($type = 'canceled')
    {
        $reasons = [];
        $rueducommerceOrder = $this->objectManager->create(
            '\RueDuCommerceSdk\Order',
            ['config' => $this->config->getApiConfig()]
        );
        $rueducommerceReasons = $rueducommerceOrder->getCancelReasons();
        if (count($rueducommerceReasons)) {
            $reasons = $rueducommerceReasons;
        }
        return $reasons;
    }

    /**
     * @param $rueducommerceOrderId
     * @param $mageOrderId
     * @param $placeDate
     * @return bool
     */
    public function sendMail($rueducommerceOrderId, $mageOrderId, $placeDate)
    {
        try {
            $body = '<table cellpadding="0" cellspacing="0" border="0">
            <tr> <td> <table cellpadding="0" cellspacing="0" border="0">
                <tr> <td class="email-heading">
                    <h1>You have a new order from RueDuCommerce.</h1>
                    <p> Please review your admin panel."</p>
                </td> </tr>
            </table> </td> </tr>
            <tr> 
                <td>
                    <h4>Merchant Order Id' . $rueducommerceOrderId . '</h4>
                </td>
                <td>
                    <h4>Magneto Order Id' . $mageOrderId . '</h4>
                </td>
                <td>
                    <h4>Order Place Date' . $placeDate . '</h4>
                </td>
            </tr>  
        </table>';
            $to_email = $this->scopeConfig->getValue('rueducommerce_config/rueducommerce_order/order_notify_email');
            $subject = 'Imp: New RueDuCommerce Order Imported';
            $senderEmail = 'rueducommerceadmin@cedcommerce.com';
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: ' . $senderEmail . '' . "\r\n";
            mail($to_email, $subject, $body, $headers);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Send Mail', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    /**
     * @param $order
     */
    public function generateInvoice($order)
    {
        try {
            $invoice = $this->objectManager->create('Magento\Sales\Model\Service\InvoiceService')
                ->prepareInvoice($order);
            $invoice->register();
            $invoice->save();
            $transactionSave = $this->objectManager->create('Magento\Framework\DB\Transaction')
                ->addObject($invoice)->addObject($invoice->getOrder());
            $transactionSave->save();
            $order->addStatusHistoryComment(__('Notified customer about invoice #%1.', $invoice->getId()))
                ->setIsCustomerNotified(false)->save();
            $order->setStatus('processing')->save();
        } catch (\Exception $e) {
            $this->logger->error('Generate Magento Invoice', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    /**
     * Ship RueDuCommerce Order
     *
     * @param  array $data
     * @return array
     */
    public function shipOrder(array $data = [])
    {
        $response = [
            'success' => false,
            'message' => []
        ];

        try {
            $order = $this->objectManager
                ->create('\RueDuCommerceSdk\Order', ['config' => $this->config->getApiConfig('order')]);
            $magentoOrder = $this->objectManager
                ->create('\Magento\Sales\Model\Order')->load($data['order_id']);
            $packed = [];
            $shipQty = [];
            foreach ($magentoOrder->getAllItems() as $orderItem) {
                $shipQty[$orderItem->getId()] = $orderItem->getQtyInvoiced();
            }
            $packed['carrier_code'] = isset($data['ShippingProvider']) ? $data['ShippingProvider'] : '';
            $packed['carrier_name'] = isset($data['ShippingProviderName']) ? $data['ShippingProviderName'] : '';
            $packed['tracking_number'] = isset($data['TrackingNumber']) ? $data['TrackingNumber'] : '';
            /*if (isset($data['ShippingProvider']) and !empty($data['ShippingProvider'])) {
                $packed['carrier_code'] = $data['ShippingProvider'];
            } else {
                throw new \Exception('ShippingProvider are missing.');
            }

            if (isset($data['TrackingNumber']) and !empty($data['TrackingNumber'])) {
                $packed['tracking_number'] = $data['TrackingNumber'];
            } else {
                throw new \Exception('TrackingNumber are missing.');
            }*/
            $status = $order->updateTrackingInfo($data['RueDuCommerceOrderMoID'], $packed);
            $this->logger->info('Ship Order Tracking Update', ['path' => __METHOD__, 'ShipData' => var_export($data), 'TrackingData' => var_export($packed), 'ShipResponseData' => var_export($status)]);
            if ($status == 'OK' || $status == 'ok' || $status == 'Ok') {
                //$status = $order->putShipOrder($data['RueDuCommerceOrderID']);
                //$this->logger->info('Ship Order Status Update', ['path' => __METHOD__, 'ShipData' => var_export($data), 'ShipResponseData' => var_export($status)]);
                //if (!$status) {
                $this->generateShipment($magentoOrder, $shipQty);
                $response['message'][] = 'Shipped successfully. ';
                $response['success'] = true;
                // Saving fulfillment data.
                $rueducommerceOrder = $this->orders->create()->load($data['order_id'], 'magento_order_id');

                $data['OrderId'] = $data['RueDuCommerceOrderID'];
                $data['Status'] = \Ced\RueDuCommerce\Model\Source\Order\Status::SHIPPED;
                $data['Response'] = $response['message'];
                $shipments = [];
                if (!empty($rueducommerceOrder->getData('shipments'))) {
                    $shipments = $this->json->jsonDecode($rueducommerceOrder->getData('shipments'));
                }
                $shipments[] = $data;

                $rueducommerceOrder->setData('shipments', $this->json->jsonEncode($shipments));
                $rueducommerceOrder->setData('status', \Ced\RueDuCommerce\Model\Source\Order\Status::SHIPPED);
                $rueducommerceOrder->save();
                //} else {
                //$response['message'][] = $status;
                //}
            } else {
                $response['message'][] = $status;
            }
        } catch (\Exception $exception) {
            $response['message'] = $exception->getMessage();
            $this->logger->error('Ship Order', ['path' => __METHOD__, 'exception' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]);
        }

        return $response;
    }

    /**
     * Cancel RueDuCommerce Order
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

        try {
            $order = $this->objectManager
                ->create('\RueDuCommerceSdk\Order', ['config' => $this->config->getApiConfig()]);
            $magentoOrder = $this->objectManager
                ->create('\Magento\Sales\Model\Order')->load($data['order_id']);
            $cancel = [];

            if (isset($data['OrderItemIds']) and !empty($data['OrderItemIds'])) {
                foreach ($data['OrderItemIds'] as $orderItemId) {
                    // Preparing cancel qty for magento credit memo
                    if (isset($orderItemId['QuantityCancelled']) and !empty($orderItemId['QuantityCancelled'])) {
                        $cancelQty = [];
                        foreach ($magentoOrder->getAllItems() as $orderItem) {
                            if ($orderItem->getSku() == $orderItemId['SKU']) {
                                $cancelQty[$orderItem->getId()] = $orderItemId['QuantityCancelled'];
                            }
                        }
                    } else {
                        throw new \Exception('QuantityCancelled are missing.');
                    }

                    // Preparing to cancel from RueDuCommerce
                    if (isset($orderItemId['OrderItemId']) and !empty($orderItemId['OrderItemId'])) {
                        $cancel['OrderItemId'] = $orderItemId['OrderItemId'];
                    } else {
                        throw new \Exception('OrderItemId are missing.');
                    }

                    if (isset($orderItemId['Reason']) and !empty($orderItemId['Reason'])) {
                        $cancel['Reason'] = $orderItemId['Reason'];
                    } else {
                        throw new \Exception('Reasons are missing.');
                    }

                    $status = $order->cancelOrderItem($cancel);
                    if ($status->getStatus() !== \RueDuCommerceSdk\Api\Response::REQUEST_STATUS_FAILURE) {
                        $this->generateCreditMemo($magentoOrder, $cancelQty);
                        $response['message'][] = $orderItemId['SKU'].' Cancelled successfully. ';
                        $response['success'] = true;
                        // Saving fulfillment data.
                        $rueducommerceOrder = $this->orders->create()->load($data['order_id'], 'magento_order_id');

                        $data['Status'] = $status->getStatus();
                        $data['Response'] = $response['message'];

                        $cancellations = [];
                        if (!empty($rueducommerceOrder->getData('cancellations'))) {
                            $cancellations = $this->json->jsonDecode($rueducommerceOrder->getData('cancellations'));
                        }
                        $cancellations[] = $data;

                        $rueducommerceOrder->setData('cancellations', $this->json->jsonEncode($cancellations));
                        $rueducommerceOrder->setData('status', \Ced\RueDuCommerce\Model\Source\Order\Status::SHIPPED);
                        $rueducommerceOrder->save();
                    } else {
                        $response['message'][] = $orderItemId['SKU']." ". $status->getError();
                    }
                }
            } else {
                throw new \Exception('OrderItemIds are missing.');
            }
        } catch (\Exception $exception) {
            $response['message'] = $exception->getMessage();
            $this->logger->error('Cancel Order', ['path' => __METHOD__, 'exception' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]);
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
        $model->setData('title', "New RueDuCommerce Orders");
        $model->setData('description', "Congratulation! You have received " . $count . " new orders form RueDuCommerce");
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
                $this->logger->error('Generate Magento Shipment', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
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

    public function generateCreditMemo($order, $cancelleditems, $shippingAmount = null)
    {
        try {
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
                'comment_text' => 'RueDuCommerce Cancelled Orders',
                'shipping_amount' => $shippingAmount,
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
                return $creditmemo->getIncrementId();
            }
        } catch (\Exception $exception) {
            $this->logger->error('Generate Magento CreditMemo', ['path' => __METHOD__, 'exception' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]);
            return false;
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
        $model->setData('title', "Failed RueDuCommerce Order");
        $model->setData('description', "You have one pending order." . $message);
        $model->setData('url', "#");
        $model->setData('is_read', 0);
        $model->setData('is_remove', 0);
        $model->getResource()->save($model);
    }

    public function processOrderItems($order)
    {

        $items = [];

        $rueducommerceOrderItemsData = json_decode($order->getOrderItems(), true); // update

        if(isset($rueducommerceOrderItemsData['order_line'])) {
            $items = $rueducommerceOrderItemsData['order_line'];
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
        $this->registry->unregister('rueducommerce_product_errors');
        if (is_array($response->getBody())) {
            try {
                $this->registry->register(
                    'rueducommerce_product_errors',
                    $response->getBody()
                );
                $feedModel = $this->feeds->create();
                $feedModel->addData(
                    [
                        'feed_id' => $response->getRequestId(),
                        'type' => $response->getResponseType(),
                        'feed_response' => $this->json->jsonEncode(
                            ['Body' => $response->getBody(), 'Errors' => $response->getError()]
                        ),
                        'status' => (string)$response->getStatus(),
                        'feed_file' => $response->getFeedFile(),
                        'response_file' => $response->getFeedFile(),
                        'feed_created_date' => $this->dateTime->date("Y-m-d"),
                        'feed_executed_date' => $this->dateTime->date("Y-m-d"),
                        'product_ids' => $this->json->jsonEncode($this->ids)
                    ]
                );
                $feedModel->save();
                return true;
            } catch (\Exception $e) {
                $this->logger->error('Save Response', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            }
        }
        return false;
    }

    public function getCountryId($iso3_code)
    {
        $country_id = substr($iso3_code, 0,2);
        $country = $this->objectManager->create('\Magento\Directory\Model\Country')->loadByCode($iso3_code);
        if($country_id = $country->getData('country_id')) {
            $country_id = $country->getData('country_id');
        }
        if (empty($country_id)) {
            $country_id = 'US';
        }
        return $country_id;
    }

    /**
     * @return bool
     */
    public function syncOrders($orderIds)
    {
        try {
            //$orderIds = $orderCollection->getColumnValues('rueducommerce_order_id');
            $orderIds = implode(',', $orderIds);
            $storeId = $this->config->getStore();
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $store = $this->storeManager->getStore($storeId);

            $orderList = $this->rueducommerce->create(
                [
                    'config' => $this->config->getApiConfig(),
                ]
            );

            $response = $orderList->getOrderByIds($orderIds);
            $count = 0;
            if (isset($response['body']['orders']) && count($response['body']['orders']) > 0) {
                //case: single purchase order
                if (!isset($response['body']['orders']['order'][0])) {
                    $response['body']['orders']['order'] = array(
                        0 => $response['body']['orders']['order'],
                    );
                }
                /*$rueducommerceOrderId = isset($response['body']['orders']['order']) ? array_column($response['body']['orders']['order'], 'order_id') : array();
                $rueducommerceOrder = $this->orders->create()
                    ->getCollection()
                    ->addFieldToFilter('rueducommerce_order_id', $rueducommerceOrderId);*/

                foreach ($response['body']['orders']['order'] as $order) {
                    $rueducommerceOrderId = $order['order_id'];
                    $rueducommerceOrder = $this->orders->create()
                        ->getCollection()
                        ->addFieldToFilter('rueducommerce_order_id', $rueducommerceOrderId)->getFirstItem();
                    $magentoOrder = $this->salesOrder->loadByIncrementId($rueducommerceOrder->getIncrementId());
                    if ($this->validateString($rueducommerceOrder->getData())) {
                        $shipping_address_street_2 = '';
                        if (isset($order['customer']['shipping_address']['street_2']) && !is_array($order['customer']['shipping_address']['street_2']))
                            $shipping_address_street_2 = $order['customer']['shipping_address']['street_2'];

                        $billing_address_street_2 = '';
                        if (isset($order['customer']['billing_address']['street_2']) && !is_array($order['customer']['billing_address']['street_2']))
                            $billing_address_street_2 = $order['customer']['billing_address']['street_2'];

                        $shipping_address_street_1 = '';
                        if (isset($order['customer']['shipping_address']['street_1']) && !is_array($order['customer']['shipping_address']['street_1']))
                            $shipping_address_street_1 = $order['customer']['shipping_address']['street_1'];

                        $billing_address_street_1 = '';
                        if (isset($order['customer']['billing_address']['street_1']) && !is_array($order['customer']['billing_address']['street_1']))
                            $billing_address_street_1 = $order['customer']['billing_address']['street_1'];

                        $shipping_address_company = '';
                        if (isset($order['customer']['shipping_address']['company']) && !is_array($order['customer']['shipping_address']['company']))
                            $shipping_address_company = $order['customer']['shipping_address']['company'];

                        $billing_address_company = '';
                        if (isset($order['customer']['billing_address']['company']) && !is_array($order['customer']['billing_address']['company']))
                            $billing_address_company = $order['customer']['billing_address']['company'];

                        $shipAddress = $this->repositoryAddress->get($magentoOrder->getShippingAddress()->getId());
                        if ($shipAddress->getId()) {
                            $shipAddress->setFirstname((isset($order['customer']['shipping_address']['firstname']) and
                                !empty($order['customer']['shipping_address']['firstname'])) ?
                                ($order['customer']['shipping_address']['firstname']) : $order['customer']['firstname'])
                                ->setLastname((isset($order['customer']['shipping_address']['lastname']) &&
                                    !empty($order['customer']['shipping_address']['lastname'])) ?
                                    $order['customer']['shipping_address']['lastname'] : $order['customer']['lastname'])
                                ->setStreet((!empty($shipping_address_street_1)) ? $shipping_address_street_1 . " " . $shipping_address_street_2 : 'N/A')
                                ->setCity(isset($order['customer']['shipping_address']['city']) ? $order['customer']['shipping_address']['city'] : 'N/A')
                                ->setCountry(isset($order['customer']['shipping_address']['city']) ? $order['customer']['billing_address']['city'] : 'N/A')
                                ->setCountryId(isset($order['customer']['shipping_address']['country_iso_code']) ? $this->getCountryId($order['customer']['billing_address']['country_iso_code']) : 'AU')
                                ->setRegion(isset($order['customer']['shipping_address']['state']) ? $order['customer']['shipping_address']['state'] : 'N/A')
                                ->setPostcode(isset($order['customer']['shipping_address']['zip_code']) ? $order['customer']['shipping_address']['zip_code'] : 'N/A')
                                ->setTelephone(isset($order['customer']['shipping_address']['phone']) &&
                                !empty($order['customer']['shipping_address']['phone']) ? $order['customer']['shipping_address']['phone'] :
                                    '+00000000000')
                                ->setCompany($shipping_address_company);
                            $this->repositoryAddress->save($shipAddress);
                        }
                        $billAddress = $this->repositoryAddress->get($magentoOrder->getBillingAddress()->getId());
                        if ($billAddress->getId()) {
                            $billAddress->setFirstname((isset($order['customer']['billing_address']['firstname']) and
                                !empty($order['customer']['billing_address']['firstname'])) ?
                                ($order['customer']['billing_address']['firstname']) : $order['customer']['firstname'])
                                ->setLastname((isset($order['customer']['billing_address']['lastname']) &&
                                    !empty($order['customer']['billing_address']['lastname'])) ?
                                    $order['customer']['billing_address']['lastname'] : $order['customer']['lastname'])
                                ->setStreet((!empty($billing_address_street_1)) ? $billing_address_street_1 . " " . $billing_address_street_2 : 'N/A')
                                ->setCity(isset($order['customer']['billing_address']['city']) ? $order['customer']['billing_address']['city'] : 'N/A')
                                ->setCountry(isset($order['customer']['billing_address']['city']) ? $order['customer']['billing_address']['city'] : 'N/A')
                                ->setCountryId(isset($order['customer']['billing_address']['country_iso_code']) ? $this->getCountryId($order['customer']['billing_address']['country_iso_code']) : 'AU')
                                ->setRegion(isset($order['customer']['billing_address']['state']) ? $order['customer']['billing_address']['state'] : 'N/A')
                                ->setPostcode(isset($order['customer']['billing_address']['zip_code']) ? $order['customer']['billing_address']['zip_code'] : 'N/A')
                                ->setTelephone(isset($order['customer']['billing_address']['phone']) &&
                                !empty($order['customer']['billing_address']['phone']) ? $order['customer']['billing_address']['phone'] :
                                    '+1123456789')
                                ->setCompany($billing_address_company);
                            $this->repositoryAddress->save($billAddress);
                        }
                        $rueducommerceOrder->setStatus($order['order_state'])->save();
                        $count++;
                        /*if( $order['order_state'] == 'CLOSED' || $order['order_state'] == 'CANCELED' || $order['order_state'] == 'REFUSED' || $order['order_state'] == 'SHIPPING'){
                            $cancelOrderOnMagento = $this->config->getCreditMemoOnMagento();
                            if($cancelOrderOnMagento == '1') {
                                $increment_id= $magentoOrder->getIncrementId();
                                $this->createCreditMemo($increment_id, $order);
                            }
                        }*/
                    }
                }
            }

            if ($count > 0) {
                $this->notificationSuccess($count);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->logger->error('Sync Order', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    /*
    Function to generate Credit Memo
    */
    public function createCreditMemo($increment_id, $result){
        try {
            $data = array();
            $order = $this->salesOrder->loadByIncrementId($increment_id);
            if ($result['order_state'] == 'REFUSED') {
                if ($order->getData()) {
                    if ($order->canCancel()) {
                        $order->cancel()->save();
                        $order->addStatusHistoryComment(__("Order $increment_id cancel On Magento because of order is refused on rueducommerce."))
                            ->setIsCustomerNotified(false)->save();
                        $this->messageManager->addSuccessMessage("Order $increment_id CANCELED Successfully.");
                        return true;
                    }
                }
            }
            if ($order->getData()) {
                if (!isset($result['order_lines']['order_line'][0])) {
                    $result['order_lines']['order_line'] = array(
                        0 => $result['order_lines']['order_line'],
                    );
                }
                $rueducommerceOrderItems = isset($result['order_lines']['order_line']) ? $result['order_lines']['order_line'] : array();
                $rueducommerceOfferSkus = array_column($rueducommerceOrderItems, 'offer_sku');
                $orderItem = $order->getItemsCollection()->getData();
                foreach ($orderItem as $item) {
                    $totalQuantityRefunded = 0;
                    $skuIndex = array_search($item['sku'], $rueducommerceOfferSkus);
                    if (!isset($rueducommerceOrderItems[$skuIndex]['refunds']['refund'][0]) && isset($rueducommerceOrderItems[$skuIndex]['refunds']['refund'])) {
                        $rueducommerceOrderItems[$skuIndex]['refunds']['refund'] = array(
                            0 => $rueducommerceOrderItems[$skuIndex]['refunds']['refund'],
                        );
                    }
                    $refundItems = isset($rueducommerceOrderItems[$skuIndex]['refunds']['refund']) ? $rueducommerceOrderItems[$skuIndex]['refunds']['refund'] : array();
                    foreach ($refundItems as $refundItem) {
                        $totalQuantityRefunded += $refundItem['quantity'];
                    }
                    if (isset($refundItems) && count($refundItems) > 0) {
                        if ((int)$item['qty_invoiced'] > 0 && ((int)$item['qty_refunded'] != (int)$item['qty_invoiced']) && ((int)$item['qty_refunded'] < $totalQuantityRefunded)) {
                            $qtyToRefund = $totalQuantityRefunded - (int)$item['qty_refunded'];
                            $data['qtys'][$item['item_id']] = (int)$qtyToRefund;
                            $shippingAmount = isset($data['shipping_amount']) ? $data['shipping_amount'] : 0;
                            $data['shipping_amount'] = $shippingAmount + $refundItem['shipping_amount'];
                        }
                    }
                }
            }
            if (isset($data['qtys']) && count($data['qtys'])) {
                if (!$order->canCreditmemo()) {
                    return true;
                }
                $creditmemo_id = $this->generateCreditMemo($order, $data['qtys'], $data['shipping_amount']);
                if ($creditmemo_id != "") {
                    $order->addStatusHistoryComment(__("Credit Memo " . $creditmemo_id . " is Successfully generated for Order :" . $increment_id . "."))
                        ->setIsCustomerNotified(false)->save();
                    $this->messageManager->addSuccessMessage("Credit Memo " . $creditmemo_id . " is Successfully generated for Order :" . $increment_id . ".");
                    return true;
                }
            }
            return $this;
        } catch (\Exception $e) {
            $this->logger->error('Create Credit Memo', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }


    /**
     * @return bool
     */
    public function acknowledgeOrders($orderIds)
    {
        try {
            //$orderIds = $orderCollection->getColumnValues('rueducommerce_order_id');
            $orderIds = implode(',', $orderIds);
            $orderList = $this->rueducommerce->create(
                [
                    'config' => $this->config->getApiConfig(),
                ]
            );

            $response = $orderList->getOrderByIds($orderIds);
            $count = 0;
            if (isset($response['body']['orders']) && count($response['body']['orders']) > 0) {
                //case: single purchase order
                if (!isset($response['body']['orders']['order'][0])) {
                    $response['body']['orders']['order'] = array(
                        0 => $response['body']['orders']['order'],
                    );
                }

                foreach ($response['body']['orders']['order'] as $order) {
                    if (!isset($order['order_lines']['order_line'][0])) {
                        $order['order_lines']['order_line'] = array(
                            0 => $order['order_lines']['order_line'],
                        );
                    }
                    $rueducommerceOrderId = $order['order_id'];
                    $rueducommerceOrder = $this->orders->create()
                        ->getCollection()
                        ->addFieldToFilter('rueducommerce_order_id', $rueducommerceOrderId)->getFirstItem();
                    if ($this->validateString($rueducommerceOrder->getData())) {
                        if (isset($order['order_lines']['order_line'])) {
                            $acceptItemsArray = [];
                            foreach ($order['order_lines']['order_line'] as $item) {
                                $lineNumber = $item['order_line_id'];
                                if ($item['order_line_state'] == "WAITING_ACCEPTANCE") {
                                    $acceptItemsArray[] = [
                                        'order_line' => [
                                            '_attribute' => [],
                                            '_value' => [
                                                'accepted' => "true",
                                                'id' => $lineNumber
                                            ]
                                        ]
                                    ];
                                }
                            }
                            $ackResponse = $this->autoOrderAccept($rueducommerceOrderId, $acceptItemsArray);
                            if (!$ackResponse && count($acceptItemsArray) > 0) {
                                $count++;
                            }
                        }
                    }
                }
            }

            if ($count > 0) {
                $this->notificationSuccess($count);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->logger->error('Acknowlege Order', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }



    /**
     * @return bool
     */
    public function syncOrdersStatus($orderIds)
    {
        try {
            //$orderIds = $orderCollection->getColumnValues('rueducommerce_order_id');
            $orderIds = implode(',', $orderIds);
            $orderList = $this->rueducommerce->create(
                [
                    'config' => $this->config->getApiConfig(),
                ]
            );

            $response = $orderList->getOrderByIds($orderIds);
            $count = 0;
            if (isset($response['body']['orders']) && count($response['body']['orders']) > 0) {
                //case: single purchase order
                if (!isset($response['body']['orders']['order'][0])) {
                    $response['body']['orders']['order'] = array(
                        0 => $response['body']['orders']['order'],
                    );
                }
                foreach ($response['body']['orders']['order'] as $order) {
                    $orderFailed = $this->orderFailed->create()->load($order['order_id'], 'rueducommerce_order_id');
                    $orderFailed->setStatus($order['order_state'])->save();
                    $count++;
                }
            }

            if ($count > 0) {
                $this->notificationSuccess($count);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->logger->error('Sync Order Status', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * @return bool
     */
    public function rejectOrCancelOrder($orderIds)
    {
        try {
            //$orderIds = $orderCollection->getColumnValues('rueducommerce_order_id');
            $orderIds = implode(',', $orderIds);
            $orderList = $this->rueducommerce->create(
                [
                    'config' => $this->config->getApiConfig(),
                ]
            );

            $response = $orderList->getOrderByIds($orderIds);
            $count = 0;
            if (isset($response['body']['orders']) && count($response['body']['orders']) > 0) {
                //case: single purchase order
                if (!isset($response['body']['orders']['order'][0])) {
                    $response['body']['orders']['order'] = array(
                        0 => $response['body']['orders']['order'],
                    );
                }

                foreach ($response['body']['orders']['order'] as $order) {
                    if (!isset($order['order_lines']['order_line'][0])) {
                        $order['order_lines']['order_line'] = array(
                            0 => $order['order_lines']['order_line'],
                        );
                    }
                    $rueducommerceOrderId = $order['order_id'];
                    if (isset($order['order_lines']['order_line'])) {
                        $rejectItemsArray = [];
                        foreach ($order['order_lines']['order_line'] as $item) {
                            $lineNumber = $item['order_line_id'];
                            if ($item['order_line_state'] == "WAITING_ACCEPTANCE") {
                                $rejectItemsArray[] = [
                                    'order_line' => [
                                        '_attribute' => [],
                                        '_value' => [
                                            'accepted' => "false",
                                            'id' => $lineNumber
                                        ]
                                    ]
                                ];
                            }
                        }
                        $ackResponse = $this->autoOrderAccept($rueducommerceOrderId, $rejectItemsArray);
                        if (!$ackResponse && count($rejectItemsArray) > 0) {
                            $count++;
                        }
                    }
                }
            }

            if ($count > 0) {
                $this->notificationSuccess($count);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->logger->error('Reject Or Cancel Order', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }


    public function refundOnRueDuCommerce($orderIncrementId = NULL, $cancelOrder = array(), $creditMemoID = NULL)
    {
        try {
            $cancelOrder = array(
                'body' => array(
                    'refunds' => array(
                        '_attribute' => array(),
                        '_value' => $cancelOrder
                    )
                )
            );
            $orderList = $this->rueducommerce->create(
                [
                    'config' => $this->config->getApiConfig(),
                ]
            );
            $refundRes = $orderList->refundOnRueDuCommerce($cancelOrder);
            $orderModel = $this->orders->create()
                ->getCollection()
                ->addFieldToFilter('increment_id', $orderIncrementId)->getFirstItem();
            $refundResData = array(
                'creditMemoId' => $creditMemoID,
                'requestData' => $cancelOrder,
                'responseData' => $refundRes
            );
            $cancelData = $orderModel->getData();
            if (isset($cancelData[0]['cancellations']) && $cancelData[0]['cancellations'] != '') {
                $cancelData = $this->json->jsonDecode($cancelData[0]['cancellations']);
            } else {
                $cancelData = null;
            }
            if (!is_array($cancelData)) {
                $cancelData = array();
            }
            array_push($cancelData, $refundResData);
            $cancelData = $this->json->jsonEncode($cancelData);
            $orderModel->setData('cancellations', $cancelData)->save();
            //$this->logger->addInfo('Credit Memo By Core', array('path' => __METHOD__, 'request_with_response' => $refundResData));
            return $refundRes;
        } catch (\Exception $e) {
            $this->logger->error('Refund On RueDuCommerce', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * Ship RueDuCommerce Order
     *
     * @param  array $data
     * @return array
     */
    public function shipOrders($rueducommerceOrders)
    {
        if (count($rueducommerceOrders) == 0) {
            $this->logger->info('Ship Order', ['path' => __METHOD__, 'ShipData' => 'No Orders To Ship.']);
            return false;
        } else {
            foreach ($rueducommerceOrders as $rueducommerceOrder) {
                $magentoOrderId = $rueducommerceOrder->getIncrementId();
                $this->order = $this->objectManager->create('\Magento\Sales\Api\Data\OrderInterface');
                $order = $this->order->loadByIncrementId($magentoOrderId);
                if ($order->getStatus() == 'complete' || $order->getStatus() == 'Complete') {
                    $return = $this->prepareShipmentData($order, $rueducommerceOrder);
                    if ($return) {
                        $this->logger->info('Ship Order Successfully', ['path' => __METHOD__, 'Magento Increment ID' => $magentoOrderId, 'Response Data' => var_export($return)]);
                    } else {
                        $this->logger->info('Ship Order Failed', ['path' => __METHOD__, 'Magento Increment ID' => $magentoOrderId, 'Response Data' => var_export($return)]);
                    }
                }
            }
        }
        return true;
    }

    /**
     * Shipment
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function prepareShipmentData($order = null, $rueducommerceOrder = null)
    {
        try {
            $carrier_name = $carrier_code = $tracking_number = '';
            foreach ($order->getShipmentsCollection() as $shipment) {
                $alltrackback = $shipment->getAllTracks();
                foreach ($alltrackback as $track) {
                    if ($track->getTrackNumber() != '') {
                        $tracking_number = $track->getTrackNumber();
                        $carrier_code = $track->getCarrierCode();
                        $carrier_name = $track->getTitle();
                        break;
                    }
                }
            }

            $purchaseOrderId = $rueducommerceOrder->getRueducommerceOrderId();
            if (empty($purchaseOrderId)) {
                $this->logger->info('Ship Order', ['path' => __METHOD__, 'ShipData' => 'Not A RueDuCommerce Order.']);
                return false;
            }

            if ($tracking_number && $rueducommerceOrder->getRueducommerceOrderId()) {
                $shippingProvider = $this->getShipmentProviders();
                $providerCode = array_column($shippingProvider, 'code');
                $carrier_code = (in_array(strtoupper($carrier_code), $providerCode)) ? strtoupper($carrier_code) : '';
                $args = ['TrackingNumber' => $tracking_number, 'ShippingProvider' => strtoupper($carrier_code), 'order_id' => $rueducommerceOrder->getMagentoOrderId(), 'RueDuCommerceOrderID' => $rueducommerceOrder->getRueducommerceOrderId(), 'ShippingProviderName' => strtolower($carrier_name)];
                $response = $this->shipOrder($args);
                $this->logger->info('Prepare Shipment Data', ['path' => __METHOD__, 'DataToShip' => json_encode($args), 'Response Data' => json_encode($response)]);
                return $response;
            }
            return false;
        } catch (\Exception $e) {
            $this->logger->error('Refund On RueDuCommerce', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    public function downloadOrderDocument($rueducommerceOrderId)
    {
        $order = $this->objectManager
            ->create('\RueDuCommerceSdk\Order', ['config' => $this->config->getApiConfig()]);
        /*$response = $order->getDocumentIds($rueducommerceOrderId);
        if (!isset($response['body']['order_documents']['order_document'][0]) && isset($response['body']['order_documents']['order_document'])) {
            $response['body']['order_documents']['order_document'] = array(
                0 => $response['body']['order_documents']['order_document']
            );
        }
        if(is_array($response) && isset($response['body']['order_documents']['order_document'])) {
            foreach ($response['body']['order_documents']['order_document'] as $document) {
                $response = $order->downloadDocument($document);
            }
        }*/
        $response = $order->downloadDocument($rueducommerceOrderId);
        if($response) {
            return true;
        } else {
            return false;
        }
    }
}
