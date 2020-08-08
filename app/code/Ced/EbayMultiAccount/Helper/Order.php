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
 * @category    Ced
 * @package     Ced_EbayMultiAccount
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\EbayMultiAccount\Helper;
/**
 * Class Order
 * @package Ced\EbayMultiAccount\Helper
 */
class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\objectManagerInterface
     */
    public $_objectManager;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $_storeManager;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $_jdecode;
    /**
     * @var \Ced\EbayMultiAccount\Model\ResourceModel\Orders\CollectionFactory
     */
    public $_ebaymultiaccountOrder;
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
    public $_product;
    /**
     * @var Data
     */
    public $datahelper;
    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    public $cartManagementInterface;
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    public $cartRepositoryInterface;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    public $customerFactory;
    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    protected $multiAccountHelper;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * Order constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\objectManagerInterface $_objectManager
     * @param \Magento\Quote\Model\QuoteFactory $quote
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Json\Helper\Data $_jdecode
     * @param \Ced\EbayMultiAccount\Model\ResourceModel\Orders\CollectionFactory $_ebaymultiaccountOrder
     * @param \Magento\Sales\Model\Service\OrderService $orderService
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     * @param \Magento\Catalog\Model\ProductFactory $_product
     * @param Data $dataHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\objectManagerInterface $_objectManager,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Json\Helper\Data $_jdecode,
        \Ced\EbayMultiAccount\Model\ResourceModel\Orders\CollectionFactory $_ebaymultiaccountOrder,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Catalog\Model\ProductFactory $_product,
        Logger $logger,
        Data $dataHelper,
        \Magento\Framework\Message\ManagerInterface $manager,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    )
    {
        $this->creditmemoLoaderFactory = $creditmemoLoaderFactory;
        $this->orderService = $orderService;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->_objectManager = $_objectManager;
        $this->_storeManager = $storeManager;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->_product = $product;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->_jdecode = $_jdecode;
        $this->customerFactory = $customerFactory;
        $this->_ebaymultiaccountOrder = $_ebaymultiaccountOrder;
        $this->_product = $_product;
        parent::__construct($context);
        $this->_coreRegistry = $registry;
        $this->datahelper = $dataHelper;
        $this->messageManager = $manager;
        $this->logger = $logger;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->currencyFactory = $currencyFactory;
    }
    /**
     * @return bool
     */
    public function getNewOrders($accountIds = array())
    {
        $store_id = $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_setting/storeid');
        $realCustomer = $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_order/real_customer');
        $customerEmail = $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_order/customer_email');
        $customerName = $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_order/customer_name');
        $customerLastname = $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_order/customer_lastname');
        $orderFetchResult = array();
        foreach ($accountIds as $accountId) {
            if ($this->_coreRegistry->registry('ebay_account'))
                $this->_coreRegistry->unregister('ebay_account');
            $account = $this->multiAccountHelper->getAccountRegistry($accountId);
            $accountName = $account->getAccountCode();
            $this->datahelper->updateAccountVariable();
            $countryDetails = $this->datahelper->getEbayMultiAccountsites($this->datahelper->siteID);
            $currencyCode = isset($countryDetails['currency'][0]) ? $countryDetails['currency'][0] : $this->_storeManager->getStore($store_id)->getBaseCurrency();
            $store_id = $account->getAccountStore();
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            $currency =  $this->currencyFactory->create()->load($currencyCode);
            $store = $this->_storeManager->getStore($store_id)->setCurrentCurrency($currency);
            $orderdata = $this->datahelper->getOrderRequestBody();
            if ($orderdata == 'error' || $orderdata == 'please fetch the token') {
                $orderFetchResult['error'] = $orderdata;
                return $orderFetchResult;
            }
            $response = $this->_jdecode->jsonDecode($orderdata);
//            print_r($response);die;
            $count = 0;
            $orderArray = [];
            $found = '';
            try {
                if (isset($response['Order'])) {
                    if (isset($response['Order'][0])) {
                        foreach ($response['Order'] as $result) {
                            $found = '';
                            $transactions = $result['TransactionArray']['Transaction'];
                            if (isset($transactions[0])) {
                                foreach ($transactions as $transaction) {
                                    if (isset($transaction['ShippingDetails']['ShipmentTrackingDetails'])) {
                                        $found = "notexist";
                                        break;
                                    }
                                }
                            } else {
                                if (isset($transactions['ShippingDetails']['ShipmentTrackingDetails'])) {
                                    $found = "notexist";
                                }
                            }
                            if ($found == '') {
                                $orderArray[] = $result;
                            }
                        }
                    } else {
                        $result = $response['Order'];
                        $transactions = $result['TransactionArray']['Transaction'];
                        if (isset($transactions[0])) {
                            foreach ($transactions as $transaction) {
                                if (isset($transaction['ShippingDetails']['ShipmentTrackingDetails'])) {
                                    $found = "notexist";
                                    break;
                                }
                            }
                        } else {
                            if (isset($transactions['ShippingDetails']['ShipmentTrackingDetails'])) {
                                $found = "notexist";
                            }
                        }
                        if ($found == '') {
                            $orderArray[] = $result;
                        }
                    }
                }
                foreach ($orderArray as $ebaymultiaccountOrder) {
                    $ebaymultiaccountOrderid = $ebaymultiaccountOrder ['OrderID'];
                    $transactions = $ebaymultiaccountOrder['TransactionArray']['Transaction'];
                    if (isset($transactions[0])) {
                        foreach ($transactions as $transaction) {
                            $email = ( $transaction['Buyer']['Email'] != 'Invalid Request') ? $transaction['Buyer']['Email'] : (isset($transaction['Buyer']['StaticAlias']) ? $transaction['Buyer']['StaticAlias'] : '') ;
                            $firstName = $transaction['Buyer']['UserFirstName'];
                            $lastName = $transaction['Buyer']['UserLastName'];
                            break;
                        }
                    } else {
                        $email = ( $transactions['Buyer']['Email'] != 'Invalid Request') ? $transactions['Buyer']['Email'] : (isset($transactions['Buyer']['StaticAlias']) ? $transactions['Buyer']['StaticAlias'] : '') ;
                        $firstName = $transactions['Buyer']['UserFirstName'];
                        $lastName = $transactions['Buyer']['UserLastName'];
                    }
                    if ($realCustomer == 0 && $customerEmail != '' && $customerName != '' && $customerLastname != '') {
                        $email = $customerEmail;
                        $firstName = $customerName;
                        $lastName = $customerLastname;
                    }
                    $customer = $this->_objectManager->get('Magento\Customer\Model\Customer')->setWebsiteId($websiteId)->loadByEmail($email);
                    $resultdata = $this->_ebaymultiaccountOrder->create()->addFieldToFilter('ebaymultiaccount_order_id', $ebaymultiaccountOrderid)->addFieldToFilter('status', ['in' => ['shipped', 'acknowledge']]);

                    if (!$this->validateString($resultdata->getData())) {
                        $ncustomer = $this->_assignCustomer($customer, $firstName, $lastName, $email, $websiteId, $ebaymultiaccountOrder);
                        if (!$ncustomer) {
                            return false;
                        } else {
                            //if($ebaymultiaccountOrder['OrderID'] == '173566217537-2070197617007') {
                                $count = $this->generateQuote($store, $ncustomer, $ebaymultiaccountOrder, $count);
                          //  }
                        }
                    }
                }
                if ($count > 0) {
                    $orderFetchResult['success'] = "You have " . $count . " new orders from eBay for account " . $accountName;
                    $this->notificationSuccess($count);
                } else {
                    $orderFetchResult['error'] = 'No New Orders Found';
                }
            } catch (\Exception $e) {
                $orderFetchResult['error'] = "Order Import has some error : Please check activity Logs";
                $this->logger->addError('In Order Fetch: ' . $e->getMessage(), ['path' => __METHOD__]);
            }
        }
        return $orderFetchResult;
    }
    /**
     * @param $result
     * @param $customer
     * @param $firstName
     * @param $email
     * @param $websiteId
     * @param $lastName
     * @return bool|\Magento\Customer\Api\Data\CustomerInterface|\Magento\Customer\Model\Customer
     */
    public function _assignCustomer($customer, $firstName, $lastName, $email, $websiteId, $result)
    {
        $order_place = date("Y-m-d");
        $realCustomer = $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_order/real_customer');
        $customerGroup = $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_order/customer_group');
        $customerPassword = $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_order/customer_password');
        if (!$this->validateString($customer->getId())) {
            if ($realCustomer == 0) {
                $password = $customerPassword;
                $groupId = $customerGroup;
            } else {
                $password = "password";
                $groupId = 1;
            }
            if($groupId == null) {
                $groupId = 1;
            }
            try {
                $websiteId = $this->_storeManager->getStore()->getWebsiteId();
                $customer = $this->_objectManager->create('Magento\Customer\Model\Customer');
                $customer->setWebsiteId($websiteId);
                $customer->setEmail($email);
                $customer->setFirstname($firstName);
                $customer->setLastname($lastName);
                $customer->setPassword($password);
                $customer->setGroupId($groupId);
                $customer->save();
                return $customer;
            } catch (\Exception $e) {
                $encodeOrderData = $this->_jdecode->jsonEncode($result);
                $orderData = [
                    'ebaymultiaccount_order_id' => $result['OrderID'],
                    'ebaymultiaccount_record_no' => $result['ShippingDetails']['SellingManagerSalesRecordNumber'],
                    'order_place_date' => $order_place,
                    'magento_id' => '',
                    'magento_order_id' => '',
                    'status' => 'failed',
                    'order_data' => $encodeOrderData,
                    'failed_order_reason' => $e->getMessage()
                ];
                $eBayModel = $this->_objectManager->create('Ced\EbayMultiAccount\Model\Orders')->loadByField('ebaymultiaccount_order_id', $result['OrderID']);
                if ($eBayModel) {
                    $eBayModel->addData($orderData)->save();
                } else {
                    $this->_objectManager->create('Ced\EbayMultiAccount\Model\Orders')->addData($orderData)->save();
                }
                $this->logger->addError('In Create Customer: has exception '.$e->getMessage(), ['path' => __METHOD__]);
                //$this->messageManager->addErrorMessage($e->getMessage());
                return false;
            }
        } else {
            $nCustomer = $this->customerRepository->getById($customer->getId());
            return $nCustomer;
        }
    }
    /**
     * @param $store
     * @param $ncustomer
     * @param $result
     * @param $resultObject
     */
    public function generateQuote($store, $ncustomer, $result, $count)
    {
        $order_place = date("Y-m-d");
        try {
            $encodeOrderData = $this->_jdecode->jsonEncode($result);
            if ($this->_coreRegistry->registry('ebay_account'))
                $account = $this->_coreRegistry->registry('ebay_account');
            $accountId = isset($account) ? $account->getId() : '';
            $orderWithoutStock = $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_order/global_setting/order_on_out_of_stock');
            $shipMethod = $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_order/global_setting/ship_method');
            //$shipMethod = 'shipbyebaymultiaccount_shipbyebaymultiaccount';
            if(isset($result['ShippingServiceSelected']['ShippingService'])
                && $result['ShippingServiceSelected']['ShippingService'] == 'FR_LivraisonEnRelaisMondialRelay') {
                $shipMethod = /*"mondialrelaypickup_24R_" . $order['relay']['id']*/'mondialrelay_pickup';
            }
            $createProduct = $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_order/global_setting/create_product');
            $shippingcost = '';
            $cart_id = $this->cartManagementInterface->createEmptyCart();
            $quote = $this->cartRepositoryInterface->get($cart_id);
            $quote->setStore($store);
            $quote->setCurrency();
            $customer = $this->customerRepository->getById($ncustomer->getId());
            $quote->assignCustomer($customer);
            $transactions = $result['TransactionArray']['Transaction'];
            if (isset($transactions[0])) {
                $transArray = $transactions;
            } else {
                $transArray[] = $transactions;
            }
            foreach ($transArray as $transaction) {
                $firstName = $transaction['Buyer']['UserFirstName'];
                $lastName = $transaction['Buyer']['UserLastName'];
                $order_place = date("Y-m-d", strtotime($transaction['CreatedDate']));
                $sku =  ( isset($transaction['Variation']['SKU']) ? $transaction['Variation']['SKU'] : ( isset($transaction['Item']['SKU']) ? $transaction['Item']['SKU'] : $transaction['Item']['ItemID'] ));
                $product_obj = $this->_objectManager->get('Magento\Catalog\Model\Product');
                $product = $product_obj->loadByAttribute('sku', $sku);
                if(!$product && $createProduct) {
                        $product = $this->_objectManager->create('Magento\Catalog\Model\Product');
                        $product->setName($transaction['Item']['Title']);
                        $product->setTypeId('simple');
                        $product->setAttributeSetId(4);
                        $product->setSku($sku);
                        $product->setWebsiteIds(array(1));
                        $product->setVisibility(1);
                        $product->setUrlKey($sku);
                        $product->setPrice([$transaction['TransactionPrice']]);
                        $product->setStockData(array(
                                'manage_stock' => 1, //manage stock
                                'is_in_stock' => 1, //Stock Availability
                                'qty' => $transaction ['QuantityPurchased'] //qty
                            )
                        );
                        $product->getResource()->save($product);
                }
                $product = $product_obj->loadByAttribute('sku', $sku);
                if ($product) {
                    $product = $this->_product->create()->load($product->getEntityId());
                    if ($product->getStatus() == '1') {
                        $stockRegistry = $this->_objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface');
                        /* Get stock item */
                        $stock = $stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
                        $stockstatus = ($stock->getQty() > 0) ? ($stock->getIsInStock() == '1' ? ($stock->getQty() >= $transaction ['QuantityPurchased'] ? true : false) : false) : false;
                        if (!$stockstatus && $orderWithoutStock == 1) {
                            $stockRegistry = $this->_objectManager->create('Magento\CatalogInventory\Api\StockRegistryInterface');
                            $product_obj = $this->_objectManager->create('Magento\Catalog\Model\Product');
                            $product = $product_obj->loadByAttribute('sku', $sku);
                            $updateQty = $transaction ['QuantityPurchased'] + 1;
                            $stock = $stockRegistry->getStockItem($product->getId());
                            $stock->setIsInStock(1);
                            $stock->setQty(intval($updateQty));
                            $stock->save();
                            $product->save();
                            $stockstatus = true;
                        }
                        if ($stockstatus) {
                            $productArray [] = [
                                'id' => $product->getEntityId(),
                                'qty' => $transaction ['QuantityPurchased']];
                            $price = $transaction['TransactionPrice'];
                            $currencyRate = $this->currencyFactory->create()->load($store->getBaseCurrency())->getAnyRate($store->getCurrentCurrency());
                            $price = ($currencyRate) ? $price / $currencyRate : $price;
                            $qty = $transaction ['QuantityPurchased'];
                            $baseprice = $qty * $price;
                            $shippingcost = $result ['ShippingServiceSelected']['ShippingServiceCost'];
                            $rowTotal = $price * $qty;
                            $product->setPrice($price)
                                ->setSpecialPrice($price)
                                ->setTierPrice([])
                                ->setBasePrice($baseprice)
                                ->setOriginalCustomPrice($price)
                                ->setRowTotal($rowTotal)
                                ->setBaseRowTotal($rowTotal);
                            $quote->addProduct($product, (int)$qty);
                        } else {
                            $orderData = [
                                'ebaymultiaccount_order_id' => $result['OrderID'],
                                'ebaymultiaccount_record_no' => $result['ShippingDetails']['SellingManagerSalesRecordNumber'],
                                'order_place_date' => $order_place,
                                'magento_id' => '',
                                'magento_order_id' => '',
                                'status' => 'failed',
                                'failed_order_reason' => "No Inventory found for Product SKU: ".$product->getSku(),
                                'order_data' => $encodeOrderData,
                                'account_id' => $accountId
                            ];
                            $eBayModel = $this->_objectManager->create('Ced\EbayMultiAccount\Model\Orders')->loadByField('ebaymultiaccount_order_id', $result['OrderID']);
                            if ($eBayModel) {
                                $eBayModel->addData($orderData)->save();
                            } else {
                                $this->_objectManager->create('Ced\EbayMultiAccount\Model\Orders')->addData($orderData)->save();
                            }
                        }
                    }
                }
            }
            if (isset($productArray)) {
                $nameArray = explode(' ', $result['ShippingAddress']['Name']);
                $firstname = $lastname = '';
                $lastArray = [];
                foreach ($nameArray as $value) {
                    if ($value != '') {
                        if ($firstname == '') {
                            $firstname = $value;
                        } else {
                            $lastArray[] = $value;
                        }       
                    }
                }
                $lastname = implode(' ', $lastArray);
                $firstname = $firstname == '' ? "." : $firstname;
                $lastname = $lastname == '' ? "." : $lastname;
                $region= is_array($result['ShippingAddress']['StateOrProvince']) ? '' : $result['ShippingAddress']['StateOrProvince'];
                if (isset($result['ShippingAddress']['Street2']) && !empty($result['ShippingAddress']['Street2']) && is_string($result['ShippingAddress']['Street2'])) {
                    $street = $result['ShippingAddress']['Street1'].' '.$result['ShippingAddress']['Street2'];
                } else {
                    $street = $result['ShippingAddress']['Street1'];
                }
                $phone = 000;
                if (isset($result['ShippingAddress']['Phone'])) {
                    if (is_array($result['ShippingAddress']['Phone'])) {
                        $phone = implode(', ', $result['ShippingAddress']['Phone']);
                        $phone = $phone ==  '' ? 0 : $phone;
                    }
                    if (is_string($result['ShippingAddress']['Phone'])) {
                        $phone = $result['ShippingAddress']['Phone'];
                    }
                }
                $shipAdd = [
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'street' => $street,
                    'city' => $result['ShippingAddress']['CityName'],
                    'country_id' => $result['ShippingAddress']['Country'],
                    'region' => $region,
                    'postcode' => $result ['ShippingAddress']['PostalCode'],
                    'telephone' => $phone,
                    'fax' => '',
                    'save_in_address_book' => 1
                ];
                $billAdd = [
                    'firstname' => $firstName,
                    'lastname' => $lastName,
                    'street' => $street,
                    'city' => $result['ShippingAddress']['CityName'],
                    'country_id' => $result['ShippingAddress']['Country'],
                    'region' => $region,
                    'postcode' => $result ['ShippingAddress']['PostalCode'],
                    'telephone' => $phone,
                    'fax' => '',
                    'save_in_address_book' => 1
                ];
                $orderData = [
                    'currency_id' => 'USD',
                    'email' => 'test@cedcommerce.com',
                    'shipping_address' => $shipAdd
                ];
                $quote->getBillingAddress()->addData($billAdd);
                $shippingAddress = $quote->getShippingAddress()->addData($shipAdd);
                $shippingAddress->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod($shipMethod);
                $quote->setPaymentMethod('paybyebaymultiaccount');
                $quote->setInventoryProcessed(false);
                $quote->save();
                $quote->getPayment()->importData([
                    'method' => 'paybyebaymultiaccount'
                ]);
                $quote->collectTotals()->save();
                foreach ($quote->getAllItems() as $item) {
                    $item->setDiscountAmount(0);
                    $item->setBaseDiscountAmount(0);
                    $item->setOriginalCustomPrice($item->getPrice())
                        ->setOriginalPrice($item->getPrice())
                        ->save();
                }
                $order = $this->cartManagementInterface->submit($quote);
                $preFix = $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_order/global_setting/order_id_prefix');
                $orderId = $preFix.$order->getIncrementId();
                $order->setIncrementId($orderId);
                if($shipMethod == 'shipbyebaymultiaccount_shipbyebaymultiaccount') {
                    $order->setShippingAmount($shippingcost)->setBaseShippingAmount($shippingcost)
                        ->setShippingInclTax($shippingcost)->setBaseShippingInclTax($shippingcost);
                }
                $order->save();
                $count = isset($order) ? $count + 1 : $count;
                foreach ($order->getAllItems() as $item) {
                    $item->setOriginalPrice($item->getPrice())
                        ->setBaseOriginalPrice($item->getPrice())
                        ->save();
                }
                // after save order
                $orderData = [
                    'ebaymultiaccount_order_id' => $result['OrderID'],
                    'ebaymultiaccount_record_no' => $result['ShippingDetails']['SellingManagerSalesRecordNumber'],
                    'order_place_date' => $order_place,
                    'magento_id' => $order->getId(),
                    'magento_order_id' => $order->getIncrementId(),
                    'status' => $result['OrderStatus'] == 'Completed' ? 'acknowledge' : $result['OrderStatus'],
                    'order_data' => $encodeOrderData,
                    'failed_order_reason' => "",
                    'account_id' => $accountId
                ];
                $eBayModel = $this->_objectManager->create('Ced\EbayMultiAccount\Model\Orders')->loadByField('ebaymultiaccount_order_id', $result['OrderID']);
                if ($eBayModel) {
                    $eBayModel->addData($orderData)->save();
                } else {
                    $this->_objectManager->create('Ced\EbayMultiAccount\Model\Orders')->addData($orderData)->save();
                }
                $this->sendMail($result['OrderID'], $order->getIncrementId(), $order_place);
                $this->generateInvoice($order);
                $orderMsg = isset($result['BuyerCheckoutMessage']) ? $result['BuyerCheckoutMessage'] : '';
                $order = $this->_objectManager->create('\Magento\Sales\Model\Order')->load($order->getId());
                $order->addStatusHistoryComment($orderMsg);
                $order->save();
            } else {
                $orderData = [
                    'ebaymultiaccount_order_id' => $result['OrderID'],
                    'ebaymultiaccount_record_no' => $result['ShippingDetails']['SellingManagerSalesRecordNumber'],
                    'order_place_date' => $order_place,
                    'magento_id' => '',
                    'magento_order_id' => '',
                    'status' => 'failed',
                    'failed_order_reason' => "No Product found for Order",
                    'order_data' => $encodeOrderData,
                    'account_id' => $accountId
                ];
                $eBayModel = $this->_objectManager->create('Ced\EbayMultiAccount\Model\Orders')->loadByField('ebaymultiaccount_order_id', $result['OrderID']);
                if ($eBayModel) {
                    $eBayModel->addData($orderData)->save();
                } else {
                    $this->_objectManager->create('Ced\EbayMultiAccount\Model\Orders')->addData($orderData)->save();
                }
            }          
            return $count;
        } catch (\Exception $e) {
            $encodeOrderData = $this->_jdecode->jsonEncode($result);
            if ($this->_coreRegistry->registry('ebay_account'))
                $account = $this->_coreRegistry->registry('ebay_account');
            $accountId = isset($account) ? $account->getId() : '';
            $orderData = [
                'ebaymultiaccount_order_id' => $result['OrderID'],
                'ebaymultiaccount_record_no' => $result['ShippingDetails']['SellingManagerSalesRecordNumber'],
                'order_place_date' => $order_place,
                'magento_id' => '',
                'magento_order_id' => '',
                'status' => 'failed',
                'failed_order_reason' => $e->getMessage(),
                'order_data' => $encodeOrderData,
                'account_id' => $accountId
            ];
            $eBayModel = $this->_objectManager->create('Ced\EbayMultiAccount\Model\Orders')->loadByField('ebaymultiaccount_order_id', $result['OrderID']);
            if ($eBayModel) {
                $eBayModel->addData($orderData)->save();
            } else {
                $this->_objectManager->create('Ced\EbayMultiAccount\Model\Orders')->addData($orderData)->save();
            }
            $this->logger->addError('In Generate Quote: '.$e->getMessage(), ['path' => __METHOD__]);
        } catch (\Error $e) {
            $encodeOrderData = $this->_jdecode->jsonEncode($result);
            if ($this->_coreRegistry->registry('ebay_account'))
                $account = $this->_coreRegistry->registry('ebay_account');
            $accountId = isset($account) ? $account->getId() : '';
            $orderData = [
                'ebaymultiaccount_order_id' => $result['OrderID'],
                'ebaymultiaccount_record_no' => $result['ShippingDetails']['SellingManagerSalesRecordNumber'],
                'order_place_date' => $order_place,
                'magento_id' => '',
                'magento_order_id' => '',
                'status' => 'failed',
                'failed_order_reason' => $e->getMessage(),
                'order_data' => $encodeOrderData,
                'account_id' => $accountId
            ];
            $eBayModel = $this->_objectManager->create('Ced\EbayMultiAccount\Model\Orders')->loadByField('ebaymultiaccount_order_id', $result['OrderID']);
            if ($eBayModel) {
                $eBayModel->addData($orderData)->save();
            } else {
                $this->_objectManager->create('Ced\EbayMultiAccount\Model\Orders')->addData($orderData)->save();
            }
            $this->logger->addError('In Generate Quote: '.$e->getMessage(), ['path' => __METHOD__]);
        }
    }
    /**
     * @param $order
     */
    public function generateInvoice($order)
    {
        try {
            $invoice = $this->_objectManager->create(
                'Magento\Sales\Model\Service\InvoiceService')->prepareInvoice(
                $order);
            $invoice->register();
            $invoice->save();
            $transactionSave = $this->_objectManager->create(
                'Magento\Framework\DB\Transaction')->addObject(
                $invoice)->addObject($invoice->getOrder());
            $transactionSave->save();
            $order->addStatusHistoryComment(__(
                'Notified customer about invoice #%1.'
                , $invoice->getId()))->setIsCustomerNotified(true)->save();
            $order->setStatus('processing')->save();
        } catch (\Exception $e) {
            $this->logger->addError('In Generate Invoice: '.$e->getMessage(), ['path' => __METHOD__]);
        }
    }
    /**
     * @param $order
     * @param $cancelleditems
     */
    public function generateShipment($order, $cancelleditems)
    {
        try {
            $shipment = $this->_prepareShipment($order, $cancelleditems);
            if ($shipment) {
                $shipment->register();
                $shipment->getOrder()->setIsInProcess(true);
                try {
                    $transactionSave = $this->_objectManager->create(
                        'Magento\Framework\DB\Transaction')->addObject(
                        $shipment)->addObject($shipment->getOrder());
                    $transactionSave->save();
                    $order->setStatus('complete')->save();
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(
                        'Error in saving shipping:'
                        . $e);
                }
            }
        } catch (\Exception $e) {
            $this->logger->addError('In Generate Shipment: '.$e->getMessage(), ['path' => __METHOD__]);
        }
    }
    /**
     * @param $order
     * @param $cancelleditems
     * @return bool
     */
    public function _prepareShipment($order, $cancelleditems)
    {
        try {
            $shipment = $this->_objectManager->get(
                'Magento\Sales\Model\Order\ShipmentFactory')->create($order, isset($cancelleditems) ? $cancelleditems : [], []);
            if (!$shipment->getTotalQty()) {
                return false;
            }
            return $shipment;
        } catch (\Exception $e) {
            $this->logger->addError('In Prepare Shipment: '.$e->getMessage(), ['path' => __METHOD__]);
        }
    }
    /**
     * @param $order
     * @param $cancelleditems
     */
    public function generateCreditMemo($order, $cancelleditems)
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
            $items = ['items' => $creditmemo,
                'do_offline' => '1',
                'comment_text' => 'EbayMultiAccount Cancelled Orders',
                'adjustment_positive' => '0',
                'adjustment_negative' => '0'];
            $creditmemoLoader->setCreditmemo($items);
            $creditmemo = $creditmemoLoader->load();
            $creditmemoManagement = $this->_objectManager->create(
                'Magento\Sales\Api\CreditmemoManagementInterface'
            );
            if ($creditmemo) {
                $creditmemo->setOfflineRequested(true);
                $creditmemoManagement->refund($creditmemo, true);
            }
        } catch (\Exception $e) {
            $this->logger->addError('In Generate Credit Memo: '.$e->getMessage(), ['path' => __METHOD__]);
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
    /**
     * @param $ebaymultiaccountOrderId
     * @param $mageOrderId
     * @param $placeDate
     * @return void
     */
    public function sendMail($ebaymultiaccountOrderId, $mageOrderId, $placeDate)
    {
        try {
            $body = '<table cellpadding="0" cellspacing="0" border="0">
                <tr> <td> <table cellpadding="0" cellspacing="0" border="0">
                    <tr> <td class="email-heading">
                        <h1>You have a new order from EbayMultiAccount.</h1>
                        <p> Please review your admin panel."</p>
                    </td> </tr>
                </table> </td> </tr>
                <tr> 
                    <td>
                        <h4>Merchant Order Id' . $ebaymultiaccountOrderId . '</h4>
                    </td>
                    <td>
                        <h4>Magneto Order Id' . $mageOrderId . '</h4>
                    </td>
                    <td>
                        <h4>Order Place Date' . $placeDate . '</h4>
                    </td>
                </tr>  
            </table>';
            $to_email = $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_order/global_setting/order_notify_email');
            $to_name = 'EbayMultiAccount Seller';
            $subject = 'Imp: New EbayMultiAccount Order Imported';
            $senderEmail = 'ebaymultiaccountadmin@cedcommerce.com';
            $senderName = 'EbayMultiAccount';
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: ' . $senderEmail . '' . "\r\n";
            mail($to_email, $subject, $body, $headers);
            return true;
        } catch (\Exception $e) {
            $this->logger->addError('In Send E-Mail: '.$e->getMessage(), ['path' => __METHOD__]);
        }
    }
    /**
     * @param $count
     * @return void
     */
    public function notificationSuccess($count)
    {
        $model = $this->_objectManager->create('\Magento\AdminNotification\Model\Inbox');
        $date = date("Y-m-d H:i:s");
        $model->setData('severity', 4);
        $model->setData('date_added', $date);
        $model->setData('title', "New eBay Orders");
        $model->setData('description', "Congratulation !! You have received " . $count . " new orders for eBay");
        $model->setData('url', "#");
        $model->setData('is_read', 0);
        $model->setData('is_remove', 0);
        $model->save();
        return true;
    }
}
