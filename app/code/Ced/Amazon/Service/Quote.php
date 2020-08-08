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
 * @package     Ced_2.3
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Service;

use Ced\Amazon\Api\Data\Order\ItemInterfaceFactory as AmazonOrderItemFactory;
use Ced\Amazon\Api\Service\ConfigServiceInterface;
use Ced\Amazon\Api\Service\ProductServiceInterface;
use Ced\Amazon\Api\Service\QuoteServiceInterface;
use Ced\Amazon\Helper\Logger;
use Ced\Amazon\Helper\Product\Inventory as AmazonInventoryService;
use Ced\Amazon\Model\MailFactory as AmazonMailFactory;
use Ced\Amazon\Model\Source\Order\Failure\Reason;
use Ced\Amazon\Registry\Order as AmazonOrderRegistry;
use Ced\Amazon\Repository\Order as AmazonOrderRepository;
use Ced\Integrator\Api\GeocodeRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Directory\Helper\Data as RegionHelper;
use Magento\Directory\Model\CurrencyFactory as DirectoryCurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry; // TODO: Replace with AmazonOrderRegistry
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Cart\CurrencyFactory as QuoteCurrencyFactory;
use Magento\Quote\Model\Quote\Address\RateFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as MagentoOrderCollectionFactory;

class Quote implements QuoteServiceInterface
{
    use \Ced\Amazon\Service\Common;

    /** @var Registry  */
    public $registry;

    /** @var SerializerInterface  */
    public $serializer;

    /** @var DataObjectFactory  */
    public $dataFactory;

    /** @var EventManagerInterface  */
    public $eventManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    public $stockRegistry;

    /** @var \Magento\Quote\Model\Quote\Address\RateFactory */
    public $rateFactory;

    /** @var RegionFactory  */
    public $regionFactory;

    /** @var RegionHelper  */
    public $regionHelper;

    /** @var \Magento\Customer\Model\AddressFactory */
    public $addressFactory;

    /** @var AmazonOrderRepository  */
    public $amazonOrderRepository;

    /** @var AmazonOrderItemFactory  */
    public $amazonOrderItemFactory;

    /** @var AmazonMailFactory  */
    public $amazonMailFactory;

    /** @var AmazonOrderRegistry  */
    public $amazonOrderRegistry;

    /** @var MagentoOrderCollectionFactory  */
    public $magentoOrderCollectionFactory;

    /** @var CartRepositoryInterface  */
    public $cartRepository;

    /** @var CartManagementInterface  */
    public $cartManagement;

    /** @var QuoteCurrencyFactory  */
    public $quoteCurrencyFactory;

    /** @var DirectoryCurrencyFactory  */
    public $directoryCurrencyFactory;

    /** @var ConfigServiceInterface  */
    public $config;

    /** @var ProductServiceInterface  */
    public $productService;

    /** @var Logger  */
    public $logger;

    /** @var AmazonInventoryService  */
    public $amazonInventoryService;

    /** @var GeocodeRepositoryInterface */
    public $geocodeRepository;

    // Data Variables:
    private $account;

    private $store;

    private $customer;

    private $imported = 0;
    private $amazonOrderId = '';
    private $importTax = false;
    private $importShippingTax = false;
    private $shippingTotal = 0.00;
    private $shippingTax = 0.00;
    private $shippingDiscount = 0.00;

    private $discountAmount = 0.00;
    private $discount = [];

    private $tax = [];
    private $orderItemsCollection = [];
    private $totalTax = 0.00;
    private $total = 0.00;

    private $itemAccepted = 0;
    private $itemOrdered = 0;
    private $bundleItems = 0;
    private $productIds = [];
    private $reason = [];
    private $customStock = [];
    private $mappedAttribute = false;

    /** @var array */
    public $regions = [];

    public function __construct(
        Registry $registry,
        SerializerInterface $serializer,
        DataObjectFactory $dataFactory,
        EventManagerInterface $eventManager,
        QuoteCurrencyFactory $quoteCurrencyFactory,
        DirectoryCurrencyFactory $directoryCurrencyFactory,
        RegionFactory $regionFactory,
        RegionHelper $regionHelper,
        RateFactory $rateFactory,
        AddressFactory $addressFactory,
        CartRepositoryInterface $cartRepository,
        CartManagementInterface $cartManagement,
        StockRegistryInterface $stockRegistry,
        MagentoOrderCollectionFactory $magentoOrderCollectionFactory,
        AmazonMailFactory $amazonMailFactory,
        AmazonOrderItemFactory $amazonOrderItemFactory,
        AmazonOrderRepository $amazonOrderRepository,
        AmazonOrderRegistry $amazonOrderRegistry,
        AmazonInventoryService $amazonInventoryService,
        Logger $logger,
        ProductServiceInterface $product,
        ConfigServiceInterface $config,
        GeocodeRepositoryInterface $geocodeRepository
    ) {
        $this->registry = $registry;
        $this->serializer = $serializer;
        $this->dataFactory = $dataFactory;
        $this->eventManager = $eventManager;
        $this->quoteCurrencyFactory = $quoteCurrencyFactory;
        $this->regionFactory = $regionFactory;
        $this->regionHelper = $regionHelper;
        $this->directoryCurrencyFactory = $directoryCurrencyFactory;
        $this->rateFactory = $rateFactory;
        $this->stockRegistry = $stockRegistry;
        $this->addressFactory = $addressFactory;
        $this->cartRepository = $cartRepository;
        $this->cartManagement = $cartManagement;
        $this->magentoOrderCollectionFactory = $magentoOrderCollectionFactory;

        $this->logger = $logger;
        $this->config = $config;
        $this->amazonInventoryService = $amazonInventoryService;
        $this->productService = $product;
        $this->geocodeRepository = $geocodeRepository;
        $this->amazonMailFactory = $amazonMailFactory;
        $this->amazonOrderRepository = $amazonOrderRepository;
        $this->amazonOrderItemFactory = $amazonOrderItemFactory;
        $this->amazonOrderRegistry = $amazonOrderRegistry;
    }

    private function init()
    {
        $this->imported = 0;

        /** @var boolean $importTax , For US tax import only for FL, GA, NC states */
        $this->importTax = $this->config->getUSTaxImport();
        $this->importShippingTax = $this->config->getShippingTaxImport();

        $this->shippingTotal = 0.00;
        $this->shippingTax = 0.00;
        $this->shippingDiscount = 0.00;

        $this->discountAmount = 0.00;
        $this->discount = [];

        $this->tax = [];
        $this->orderItemsCollection = [];
        $this->totalTax = 0.00;
        $this->total = 0.00;

        $this->itemAccepted = 0;
        $this->itemOrdered = 0;
        $this->bundleItems = 0;
        $this->productIds = [];
        $this->reason = [];
        $this->customStock = [];
    }

    public function setRegions($regions = [])
    {
        $this->regions = $regions;
    }

    /**
     * Add Items To Quote
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Amazon\Sdk\Api\Order $order
     * @param \Amazon\Sdk\Api\Order\ItemList $items
     * @throws LocalizedException
     */
    private function addItems($quote, $order, $items)
    {
        $account = $this->getAccount();
        // Getting mapped inventory attribute for this account.
        $this->mappedAttribute = false;
        $mappedInventoryAttribute = $this->config->getInventoryAttribute();
        if (isset($mappedInventoryAttribute[$account->getId()])) {
            $this->mappedAttribute = $mappedInventoryAttribute[$account->getId()];
        }

        // Flags for inclusive taxes, Taxes are inclusive in Europe but exclusive in US
        $inclusiveTax = (\Amazon\Sdk\Marketplace::getRegionByMarketplaceId($order->getMarketplaceId()) ==
            \Amazon\Sdk\Marketplace::REGION_EUROPE) ? true : false;
        $inclusiveShippingTax = (\Amazon\Sdk\Marketplace::getRegionByMarketplaceId($order->getMarketplaceId()) ==
            \Amazon\Sdk\Marketplace::REGION_EUROPE) ? true : false;

        foreach ($items as $index => $item) {
            $item = $this->canceled($item, $items->getItems());
            $sku = $items->getSellerSKU($index);
            $qty = isset($item['QuantityOrdered']) ? $item['QuantityOrdered'] : 0;
            if (isset($item['SellerSKU'], $item['QuantityOrdered']) && $item['QuantityOrdered'] > 0) {
                $this->itemOrdered++;
                $qty = $item['QuantityOrdered'];
                /** @var \Magento\Catalog\Model\Product $product */
                $product = $this->productService->find($item['SellerSKU']);

                // Creating unavailable product during order import
                if (($this->config->createUnavailableProduct()) && (!isset($product) || empty($product))) {
                    $product = $this->productService->create($items, $index);
                }

                if (isset($product) && !empty($product)) {
                    if ($product->getStatus() == '1' || $this->config->createBackorder()) {
                        $this->productIds[$product->getId()] = $product->getId();
                        $sku = $product->getSku();

                        if ($this->mappedAttribute) {
                            /* Get stock item from mapped inventory attribute */
                            $stockInAttribute = (int)$product->getData($this->mappedAttribute);
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
                            // Fixing negative error
                            $this->discount[$sku] = abs((float)$items->getPromotionDiscount($index, true));
                            $this->discountAmount += $this->discount[$sku];

                            // Fixing negative error
                            $this->shippingDiscount += abs((float)$items->getShippingDiscount($index, true));

                            $shippingTax = 0.00;
                            if ($this->importShippingTax) {
                                $shippingTax = (float)$items->getShippingTax($index, true);
                                $this->shippingTax += $shippingTax;
                            }

                            $shippingAmount = (float)$items->getShippingPrice($index, true);
                            if ($inclusiveShippingTax) {
                                $shippingAmount = $shippingAmount - $shippingTax;
                            }

                            $this->shippingTotal += $shippingAmount;

                            $price = $items->getItemPrice($index);

                            $this->tax[$sku] = $items->getItemTax($index, true);
                            if ($inclusiveTax) {
                                $taxPerItem = $items->getTaxPerItem($index, true);
                                $price = $price - $taxPerItem;
                            }

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
                                ->setBaseRowTotal($rowTotal);

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
                                            if ($selection->getIsDefault()) {
                                                /**
                                                 * @var \Magento\Bundle\Api\Data\LinkInterface $selection
                                                 */
                                                $bundleOptions[$option->getId()][] = $selection->getId();
                                                $bundleOptionsQty[$option->getId()][] = $selection->getQty();
                                                $this->bundleItems++;
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
                            $addedItem = $quote->addProduct($product, $request);

                            if ($addedItem instanceof \Magento\Quote\Model\Quote\Item) {
                                $addedItem->setData("amazon_order_id", $order->getAmazonOrderId());
                                $addedItem->setData("amazon_order_item_id", $items->getOrderItemId($index));
                            }

                            // Adding custom stock for update
                            if ($this->mappedAttribute && $stockStatus) {
                                if (isset($this->customStock[$sku])) {
                                    $this->customStock[$sku] += $qty;
                                } else {
                                    $this->customStock[$sku] = $qty;
                                }
                            }

                            // Adding order items in OrderItemCollection variable
                            $this->orderItemsCollection[$items->getOrderItemId($index)] = [
                                "sku" => $sku,
                                "asin" => $items->getASIN($index),
                                "order_id" => $this->amazonOrderId,
                                "order_item_id" => $items->getOrderItemId($index),
                                "customized_url" => $items->getCustomizedURL($index),
                                "qty_ordered" => $items->getQuantityOrdered($index),
                                "qty_shipped" => $items->getQuantityShipped($index),
                            ];
                            $this->itemAccepted++;
                        } else {
                            $this->reason[Reason::ERROR_OUT_OF_STOCK_CODE] =
                                sprintf(Reason::ERROR_MESSAGE_OUT_OF_STOCK, $item['SellerSKU']);
                        }
                    } else {
                        $this->reason[Reason::ERROR_NOT_ENABLED_CODE] =
                            sprintf(
                                Reason::ERROR_MESSAGE_NOT_ENABLED,
                                $item['SellerSKU'],
                                $this->getStore()->getName()
                            );
                    }
                } else {
                    $this->reason[Reason::ERROR_DOES_NOT_EXISTS_CODE] =
                        sprintf(
                            Reason::ERROR_MESSAGE_DOES_NOT_EXISTS,
                            $item['SellerSKU'],
                            $this->getStore()->getName()
                        );
                }
            } else {
                $this->reason[Reason::ERROR_ITEM_DATA_NOT_AVAILABLE_CODE] =
                    sprintf(Reason::ERROR_MESSAGE_ITEM_DATA_NOT_AVAILABLE, $sku, $qty);
            }
        }
    }

    /**
     * Create Quote
     * @param \Amazon\Sdk\Api\Order|null $order
     * @return int
     * @throws \Exception
     */
    public function create($order = null)
    {
        try {
            $this->amazonOrderRegistry->clear();
            $this->amazonOrderRegistry->setOrder($order);
            $this->init();
            $customer = $this->getCustomer();

            /** @var string $amazonOrderId */
            $this->amazonOrderId = $order->getAmazonOrderId();

            /** @var \Amazon\Sdk\Api\Order\ItemList $items */
            $items = $order->fetchItems(true);

            $this->amazonOrderRegistry->setItems($items);

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
                ->setGlobalCurrencyCode($order->getCurrencyCode())
                ->setBaseCurrencyCode($order->getCurrencyCode())
                ->setStoreCurrencyCode($order->getCurrencyCode())
                ->setQuoteCurrencyCode($order->getCurrencyCode())
                ->setStoreToBaseRate(1.00)
                ->setStoreToQuoteRate(1.00)
                ->setBaseToGlobalRate(1.00)
                ->setBaseToQuoteRate(1.00);
            // Overriding base currency
            $this->getStore()->setBaseCurrency($directoryCurrency);

            $cartId = $this->cartManagement->createEmptyCart();
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->cartRepository->get($cartId);
            $quote->setStore($this->getStore());

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
            $name = isset($address['Name']) && !empty($address['Name']) ?
                explode(' ', $address['Name'], 2) :
                explode(' ', (string)$order->getBuyerName(), 2);
            if ($this->config->getGuestCustomer()) {
                $quote->setCustomerFirstname($this->getValue(0, $name, 'N/A'));
                $quote->setCustomerLastname($this->getValue(1, $name, 'N/A'));
                $quote->setCustomerEmail($this->email($order));
                $quote->setCustomerIsGuest(true);
            } else {
                $quote->assignCustomer($this->getCustomer());
            }

            $quote->setCustomerNoteNotify(false);

            // Adding Items to Quote
            $this->addItems($quote, $order, $items);

            /** @var \Magento\Quote\Model\ResourceModel\Quote\Item[] $qouteItems */
            $quoteItems = $quote->getAllItems();

            $shipAddress = [];
            // Condition for full order acknowledge. Partial not allowed as order update feature is not present.
            if ($this->itemAccepted > 0 && count($quoteItems) >= ($this->itemOrdered + $this->bundleItems)) {
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
                        // BUG: Cause exception if billing country is not allowed on website
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

                $this->shippingTotal = ($this->shippingTotal >= $this->shippingDiscount) ?
                    ($this->shippingTotal - $this->shippingDiscount) : $this->shippingTotal;

                $this->registerShippingAmount($this->shippingTotal);
                $this->registerShippingMethod($order->getShipServiceLevel());

                $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
                    ->setShippingMethod($this->getAccount()->getShippingMethod());

                /** @var \Magento\Quote\Model\Quote\Address\Rate|null $rate */
                $rate = $shippingAddress->getShippingRateByCode($this->getAccount()->getShippingMethod());
                if (!$rate instanceof \Magento\Quote\Model\Quote\Address\Rate) {
                    $rate = $this->rateFactory->create();
                }

                $rate->setCode($this->getAccount()->getShippingMethod())
                    ->setMethod(\Ced\Amazon\Model\Carrier\Shipbyamazon::METHOD_CODE)
                    ->setMethodTitle($order->getShipServiceLevel())
                    ->setCarrier(\Ced\Amazon\Model\Carrier\Shipbyamazon::CARRIER_CODE)
                    ->setCarrierTitle(\Ced\Amazon\Model\Carrier\Shipbyamazon::CARRIER_TITLE)
                    ->setPrice($this->shippingTotal)
                    ->setAddress($shippingAddress);
                $shippingAddress->addShippingRate($rate);

                $quote->setPaymentMethod($this->getAccount()->getPaymentMethod());
                $quote->setInventoryProcessed(false);
                $quote->getPayment()->importData([
                    'method' => $this->getAccount()->getPaymentMethod()
                ]);

//                print 'shiiping address';
//                print_r($quote->getShippingAddress()->getAppliedTaxes());
//                die();

                // Update quote items
                $this->updateQuoteItems($quote, $order);

                // Updating the reserve order id (increment id)
                $quote->reserveOrderId();
                $reservedOrderId = $quote->getReservedOrderId();
                $reservedOrderId = $this->generateIncrementId($reservedOrderId, $order);
                $quote->setReservedOrderId($reservedOrderId);

                if ($this->discountAmount > 0) {
                    $total = $quote->getBaseSubtotal();
                    $quote->collectTotals();
                    $canAddItems = $quote->isVirtual() ? ('billing') : ('shipping');
                    foreach ($quote->getAllAddresses() as $address) {
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

                        $quote->setGrandTotal($quote->getBaseSubtotal() - $this->discountAmount)
                            ->setBaseGrandTotal($quote->getBaseSubtotal() - $this->discountAmount)
                            ->setSubtotalWithDiscount($quote->getBaseSubtotal() - $this->discountAmount)
                            ->setBaseSubtotalWithDiscount($quote->getBaseSubtotal() - $this->discountAmount)
                            ->save();

                        if ($address->getAddressType() == $canAddItems) {
                            $address->setSubtotalWithDiscount((float)$address->getSubtotalWithDiscount() -
                                $this->discountAmount);
                            $address->setGrandTotal((float)$address->getGrandTotal() - $this->discountAmount);
                            $address->setBaseSubtotalWithDiscount((float)$address->getBaseSubtotalWithDiscount() -
                                $this->discountAmount);
                            $address->setBaseGrandTotal((float)$address->getBaseGrandTotal() - $this->discountAmount);
                            if ($address->getDiscountDescription()) {
                                $address->setDiscountAmount(-($address->getDiscountAmount() - $this->discountAmount));
                                $address->setDiscountDescription($address->getDiscountDescription() .
                                    ', Amazon Discount');
                                $address->setBaseDiscountAmount(-($address->getBaseDiscountAmount() -
                                    $this->discountAmount));
                            } else {
                                $address->setDiscountAmount(-($this->discountAmount));
                                $address->setDiscountDescription('Amazon Discount');
                                $address->setBaseDiscountAmount(-($this->discountAmount));
                            }

                            $address->save();
                        }//end: if
                    } //end: foreach

                    foreach ($quote->getAllItems() as $item) {
                        $sku = $item->getSku();
                        $discountValue = isset($this->discount[$sku]) ? $this->discount[$sku] : 0.00;
                        $item->setDiscountAmount($discountValue);
                        $item->setBaseDiscountAmount($discountValue)->save();
                    }
                } else {
                    $quote->setCouponCode('')->collectTotals()->save();
                }

                // forcefully changing the currency for quote
                $quote->setForcedCurrency($directoryCurrency);
                $quote->setBaseCurrencyCode($order->getCurrencyCode());
                $quote->setGlobalCurrencyCode($order->getCurrencyCode());
                $quote->setStoreCurrencyCode($order->getCurrencyCode());
                $quote->setQuoteCurrencyCode($order->getCurrencyCode());
                $quote->setCurrency($quoteCurrency);

                $error = "";
                try {
                    /** @var \Magento\Sales\Model\Order $magentoOrder */
                    $magentoOrder = $this->cartManagement->submit($quote);
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                    $magentoOrder = $this->findByIncrementId($reservedOrderId);
                }

                if (isset($magentoOrder)) {
                    // 2019-06-20T14:23:49.894Z, use gmdate to avoid GMT add or delete hours
                    $createdAt = gmdate("Y-m-d H:i:s", strtotime($order->getPurchaseDate()));
                    $magentoOrder
                        ->setCreatedAt($createdAt)
                        ->setBaseDiscountAmount($this->discountAmount)//-$discountAmount
                        ->setDiscountAmount($this->discountAmount)//-$discountAmount
                        ->setSubtotalInclTax($this->total + $this->totalTax)
                        ->setBaseSubtotalInclTax($this->total + $this->totalTax)
                        ->setGrandTotal($this->total + $this->totalTax - $this->discountAmount +
                            $this->shippingTax + $this->shippingTotal)
                        ->setBaseGrandTotal($this->total + $this->totalTax - $this->discountAmount +
                            $this->shippingTax + $this->shippingTotal)
                        ->setShippingInclTax($this->shippingTax + $this->shippingTotal)
                        ->setShippingTaxAmount($this->shippingTax)
                        ->setBaseShippingTaxAmount($this->shippingTax)
                        ->setShippingAmount($this->shippingTotal)
                        ->setBaseShippingAmount($this->shippingTotal)
                        ->setBaseShippingInclTax($this->shippingTax + $this->shippingTotal)
                        ->setShippingInclTax($this->shippingTax + $this->shippingTotal)
                        ->setTaxAmount($this->totalTax + $this->shippingTax)
                        ->setBaseTaxAmount($this->totalTax + $this->shippingTax);

                    // Completing Order if Shipped
                    if ($order->getOrderStatus() == \Amazon\Sdk\Api\Order::ORDER_STATUS_SHIPPED) {
                        $magentoOrder->setState(\Magento\Sales\Model\Order::STATE_COMPLETE)
                            ->setStatus(\Magento\Sales\Model\Order::STATE_COMPLETE);
                    }

                    $this->addMessage("Order imported successfully via %1", $magentoOrder);

                    // Overriding order details. Data is different from events triggered.
                    $magentoOrder->save();

                    foreach ($magentoOrder->getAllItems() as $oItems) {
                        $this->orderItemsCollection[$oItems->getAmazonOrderItemId()]['magento_order_item_id'] =
                            $oItems->getItemId();
                    }

                    $this->imported = isset($magentoOrder) ? $this->imported + 1 : $this->imported;

                    // after save order
                    $purchaseDate = date("Y-m-d H:i:s", strtotime($order->getPurchaseDate()));
                    $lastUpdateDate = date("Y-m-d H:i:s", strtotime($order->getLastUpdateDate()));
                    $status = $order->getOrderStatus();

                    try {
                        // Updating order in Amazon Order Table
                        /** @var \Ced\Amazon\Model\Order $marketplaceOrder */
                        $marketplaceOrder = $this->amazonOrderRepository
                            ->getByPurchaseOrderId($order->getAmazonOrderId());
                        $marketplaceOrder->setData(\Ced\Amazon\Model\Order::COLUMN_PO_ID, $order->getAmazonOrderId());
                        $marketplaceOrder->setData(\Ced\Amazon\Model\Order::COLUMN_LAST_UPDATE_DATE, $lastUpdateDate);
                        $marketplaceOrder->setData(\Ced\Amazon\Model\Order::COLUMN_PO_DATE, $purchaseDate);
                        $marketplaceOrder->setData(\Ced\Amazon\Model\Order::COLUMN_STATUS, $status);
                        $marketplaceOrder
                            ->setData(\Ced\Amazon\Model\Order::COLUMN_MARKETPLACE_ID, $order->getMarketplaceId());
                        $marketplaceOrder->setData(
                            \Ced\Amazon\Model\Order::COLUMN_FULFILLMENT_CHANNEL,
                            $order->getFulfillmentChannel()
                        );
                        $marketplaceOrder->setData(
                            \Ced\Amazon\Model\Order::COLUMN_SALES_CHANNEL,
                            $order->getSalesChannel()
                        );
                        $marketplaceOrder->setData(
                            \Ced\Amazon\Model\Order::COLUMN_MAGENTO_ORDER_ID,
                            $magentoOrder->getId()
                        );
                        $marketplaceOrder->setData(
                            \Ced\Amazon\Model\Order::COLUMN_MAGENTO_INCREMENT_ID,
                            $magentoOrder->getIncrementId()
                        );
                        $marketplaceOrder->setData(
                            \Ced\Amazon\Model\Order::COLUMN_ORDER_DATA,
                            $this->serializer->serialize($order->getData())
                        );
                        $marketplaceOrder->setData(
                            \Ced\Amazon\Model\Order::COLUMN_FAILURE_REASON,
                            $this->serializer->serialize($this->reason)
                        );
                        // TODO: Remove, use item table
                        $marketplaceOrder->setData(
                            \Ced\Amazon\Model\Order::COLUMN_ORDER_ITEMS,
                            $this->serializer->serialize($items->getItems())
                        );
                        $this->amazonOrderRepository->save($marketplaceOrder);
                    } catch (\Exception $e) {
                        $this->logger->error($e->getMessage(), ['path' => __METHOD__]);
                    }

                    // Save order items in Amazon Item Table
                    $this->saveItems();

                    if (!empty($this->customStock) && $this->mappedAttribute) {
                        foreach ($this->customStock as $productSku => $stockQuantity) {
                            // TODO: FIX: do not load the product again.
                            /** @var \Magento\Catalog\Model\Product $product */
                            $product = $this->productService->find($productSku);
                            $stockInAttribute = (int)$product->getData($this->mappedAttribute);
                            $product->setCustomAttribute($this->mappedAttribute, $stockInAttribute - $stockQuantity);
                            $this->productService->update($product, [$this->mappedAttribute]);
                        }
                    }

                    // Adding extension attributes and dispatching import after event
                    $magentoOrder->getExtensionAttributes()
                        ->setAmazonOrderId($this->amazonOrderId)
                        ->setAmazonOrderPlaceDate($purchaseDate);
                    $this->eventManager->dispatch("amazon_order_import_after", ['order' => $magentoOrder]);

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
                        $mail = $this->amazonMailFactory->create();
                        $mail->send($data);
                    }

                    $autoInvoice = $this->config->getAutoInvoice();
                    if ($autoInvoice) {
                        $this->invoice($magentoOrder);
                    }

                    if ($this->config->getInventorySync()) {
                        $this->amazonInventoryService->update(
                            $this->productIds,
                            true,
                            \Ced\Amazon\Model\Source\Queue\Priorty::HIGH
                        );
                    }
                } else {
                    throw new LocalizedException(__('Failed to create order in Magento. ' . $error));
                }
            } else {
                $items = isset($items) ? $items->getItems() : [];
                $this->save($order, $this->reason, $items);
            }
        } catch (\Exception $exception) {
            // Save Order
            $this->reason[Reason::ERROR_ORDER_IMPORT_EXCEPTION_CODE] =
                Reason::ERROR_MESSAGE_ORDER_IMPORT_EXCEPTION . ' [' . $exception->getMessage() . ' ]';
            $items = isset($items) ? $items->getItems() : [];
            $this->save($order, $this->reason, $items);

            // Removing quote on failure
            if (isset($quote) && $quote instanceof CartInterface) {
                $this->cartRepository->delete($quote);
            }

            // Add Logging
            $level = $this->config->getLoggingLevel();
            $log = [
                'path' => __METHOD__,
                'message' => $exception->getMessage(),
            ];

            if (isset($amazonOrderId)) {
                $log['amazon_order_id'] = $amazonOrderId;
            }

            if ($level < 100) {
                $log['trace'] = $exception->getTraceAsString();
            }
            $this->logger->error("Order #{$this->amazonOrderId} import failure exception.", $log);
            return false;
        }

        return true;
    }

    /**
     * Update Magento Quote Items
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Amazon\Sdk\Api\Order $order
     * @throws \Exception
     */
    private function updateQuoteItems($quote, $order)
    {
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($quote->getAllItems() as $item) {
            $qty = (float)$item->getQty();
            $sku = $item->getSku();
            $this->total += ($qty * $item->getPrice());
            $discountValue = isset($this->discount[$sku]) ? $this->discount[$sku] : 0.00;
            $item->setDiscountAmount($discountValue);
            $item->setBaseDiscountAmount($discountValue);
            $item->setOriginalCustomPrice($item->getPrice());
            $item->setOriginalPrice($item->getPrice());

            if (isset($this->tax[$sku]) && $this->tax[$sku] > 0) {
                if ($this->importTax && $order->getMarketplaceId() == \Amazon\Sdk\Marketplace::MARKETPLACE_ID_US) {
                    if (isset($regionId) && in_array($regionId, $this->regions)) {
                        $value = $this->tax[$sku];
                        $this->totalTax += $value;

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
                    $value = $this->tax[$sku];
                    $this->totalTax += $value;

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
                }
            }

            $item->save();
        }
    }

    /**
     * Save Amazon Order Items in Table
     */
    private function saveItems()
    {
        foreach ($this->orderItemsCollection as $orderSku => $orderItemData) {
            try {
                /** @var \Ced\Amazon\Model\Order\Item $orderItem */
                $orderItem = $this->amazonOrderItemFactory->create();
                $orderItem->setData($orderItemData)->save();
            } catch (\Exception $exception) {
                $this->logger->error(
                    " Items of #{$this->amazonOrderId} import failure exception.",
                    [
                        'path' => __METHOD__,
                        'message' => $exception->getMessage(),
                        'stack_trace' => $exception->getTraceAsString()
                    ]
                );
            }
        }
    }

    /**
     * Save Order in Amazon
     * @param \Amazon\Sdk\Api\Order $order
     * @param array $reason
     * @param array $items
     * @param string $status
     * @return null|\Ced\Amazon\Model\Order
     * @throws \Exception
     */
    public function save(
        \Amazon\Sdk\Api\Order $order,
        $reason = [],
        $items = [],
        $status = \Ced\Amazon\Model\Source\Order\Status::FAILED
    ) {
        $marketplaceOrder = null;
        try {
            /** @var \Ced\Amazon\Model\Order $marketplaceOrder */
            $marketplaceOrder = $this->amazonOrderRepository->getByPurchaseOrderId($order->getAmazonOrderId());
            $marketplaceOrder->setData(
                \Ced\Amazon\Model\Order::COLUMN_STATUS,
                $status
            );
            $marketplaceOrder->setData(
                \Ced\Amazon\Model\Order::COLUMN_FAILURE_REASON,
                $this->serializer->serialize($reason)
            );
            $marketplaceOrder->setData(
                \Ced\Amazon\Model\Order::COLUMN_ORDER_ITEMS,
                $this->serializer->serialize($items)
            );
            $this->amazonOrderRepository->save($marketplaceOrder);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['path' => __METHOD__]);
        }
        return $marketplaceOrder;
    }

    /**
     * Check if item line is cancelled
     * @param $check
     * @param array $items
     * @return array
     */
    private function canceled($check, $items = [])
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

            if (empty($regionId) && $this->regionHelper->isRegionRequired($countryId)) {
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

    private function getTaxClassId()
    {
        return 0;
    }

    /**
     * Set Quote Store
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @return void
     */
    public function setStore($store)
    {
        $this->store = $store;
    }

    /**
     * Set Quote Account
     * @param \Ced\Amazon\Api\Data\AccountInterface $account
     * @return void
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }

    /**
     * Set Quote Customer
     * @param \Magento\Customer\Api\Data\CustomerInterface|null $customer
     * @return void
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * Get Quote Store
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * Get Quote Account
     * @return \Ced\Amazon\Api\Data\AccountInterface
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Get Quote Customer
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getCustomer()
    {
        return  $this->customer;
    }
}
