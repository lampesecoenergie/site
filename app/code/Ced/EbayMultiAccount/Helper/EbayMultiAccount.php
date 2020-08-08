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
 * @package   Ced_EbayMultiAccount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Helper;

class EbayMultiAccount extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    public $_curl;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;
    /**
     * @var mixed
     */
    public $scopeConfigManager;
    /**
     * @var mixed
     */
    public $adminSession;
    /**
     * @var \Magento\Framework\Message\Manager
     */
    public $messageManager;
    /**
     * DirectoryList
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    public $directoryList;
    /**
     * Json Parser
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $json;
    /**
     * @var \Magento\Framework\HTTP\Adapter\Curl
     */
    public $_resource;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var
     */
    public $_siteID;

    public $token;

    /**
     * EbayMultiAccount constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Message\Manager $manager
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Json\Helper\Data $json
     * @param \Magento\Directory\Model\CountryFactory $country
     * @param \Magento\Framework\HTTP\Adapter\Curl $curl
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\Manager $manager,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Directory\Model\CountryFactory $country,
        \Ced\EbayMultiAccount\Helper\Logger $logger,
        \Magento\Framework\HTTP\Adapter\Curl $curl,
        \Magento\Framework\Registry $registry,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->objectManager = $objectManager;
        $this->_resource = $curl;
        parent::__construct($context);
        $this->multiAccountHelper = $multiAccountHelper;
        $this->_coreRegistry = $registry;
        $account = false;
        if ($this->_coreRegistry->registry('ebay_account')) {
            $account = $this->_coreRegistry->registry('ebay_account');
        }
        $this->messageManager = $manager;
        $this->directoryList = $directoryList;
        $this->json = $json;
        $this->_country = $country;
        $this->logger = $logger;
        $this->_storeManager = $storeManager;
        $this->adminSession = $this->objectManager->create('Magento\Backend\Model\Session');
        $this->configResourceModel = $this->objectManager->create('\Magento\Config\Model\ResourceModel\Config');
        $this->token = ($account) ? trim($account->getAccountToken()) : $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_setting/token');
        $this->_siteID = ($account) ? trim($account->getAccountLocation()) : $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_setting/location');
    }

    public function updateAccountVariable() {
        $account = false;
        if ($this->_coreRegistry->registry('ebay_account')) {
            $account = $this->_coreRegistry->registry('ebay_account');
        }
        $this->environment = ($account) ? trim($account->getAccountEnv()) : $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_setting/environment');
        $this->token = ($account) ? trim($account->getAccountToken()) : $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_setting/token');
        $this->siteID = ($account) ? trim($account->getAccountLocation()) : $this->scopeConfig->getValue('ebaymultiaccount_config/ebaymultiaccount_setting/location');
    }

    /**
     * @param $product
     * @param $type
     * @return array
     */
    public function prepareData($product, $type=null)
    {
        try {
            $account = $this->_coreRegistry->registry('ebay_account');
            $profileIdAccAttr = $this->multiAccountHelper->getProfileAttrForAcc($account->getId());
            $allImg = $shippingDetails = $internationalShippingDetails = [];
            $countryDetails = $this->objectManager->get('Ced\EbayMultiAccount\Helper\Data')->getEbayMultiAccountsites($this->_siteID);
            $site = $countryDetails['name'];
            $country = $this->scopeConfig->getValue('ebaymultiaccount_config/product_upload/item_country'); //$countryDetails['abbreviation'];
            $currency = $this->scopeConfig->getValue('ebaymultiaccount_config/product_upload/item_currency');//$countryDetails['currency'][0];

            $pictureUrls = [];
            $xmlArray = [];
            if (is_string($product)) {
                $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($product);
            }

            $profileId = $product->getData($profileIdAccAttr);
            $profileData = $this->objectManager->get('Ced\EbayMultiAccount\Model\Profile')->load($profileId);
            $accountConfigurationId = $profileData->getAccountConfigurationId();
            $profileConfig = $this->objectManager->get('Ced\EbayMultiAccount\Model\AccountConfig')->load($accountConfigurationId);
            $paymentMethods = $this->getConfigData('payment_method', $profileConfig);
            $paypalEmail = $this->getConfigData('paypal_email', $profileConfig);

            $postalCode = $this->scopeConfig->getValue('ebaymultiaccount_config/product_upload/postal_code');
            $itemLocation = $this->scopeConfig->getValue('ebaymultiaccount_config/product_upload/item_location');
            $maxQty = $this->scopeConfig->getValue('ebaymultiaccount_config/product_upload/max_qty');

            $returnDays = $this->getConfigData('return_days', $profileConfig);
            $returnAccepted = $this->getConfigData('return_accepted', $profileConfig);
            $returnDescription = $this->getConfigData('return_description', $profileConfig);
            $refundType = $this->getConfigData('refund_type', $profileConfig);
            $shipCostPaidby = $this->getConfigData('ship_cost_paidby', $profileConfig);

            $serviceType = $this->getConfigData('service_type', $profileConfig);
            $shippingMethods = $this->getConfigData('domesticService', $profileConfig);
            $internationalShipping = $this->getConfigData('internationalService', $profileConfig);
            $globalShipping = $this->getConfigData('global_shipping', $profileConfig);
            $shipToLocation = $this->getConfigData('ship_to_location', $profileConfig);
            $excludedArea = $this->getConfigData('excluded_area', $profileConfig);
            $salesTaxRate = $this->getConfigData('sale_tax_rate', $profileConfig);
            $salesTaxState = $this->getConfigData('sale_tax_state', $profileConfig);
            $freeShipping = $this->getConfigData('free_shipping', $profileConfig);
            $shippingIncludes = $this->getConfigData('shipping_includes', $profileConfig);

            $productShippingSetting = $product->getEbayDomesticShipping();
            if($productShippingSetting != null && $productShippingSetting) {
                $shippingMethods = $productShippingSetting;
            }

            if (empty($paymentMethods) || ($returnAccepted == 'ReturnsAccepted' && empty($shipCostPaidby)) || empty($serviceType) || empty($postalCode) || empty($shippingMethods)) {
                $configEmptyFileds = '';
                $configEmptyFileds .= (empty($paymentMethods) ? 'Payment Methods, ' : '');
                $configEmptyFileds .= (($returnAccepted == 'ReturnsAccepted' && empty($shipCostPaidby)) ? 'Shipment Cost Paid By, ' : '');
                $configEmptyFileds .= (empty($serviceType) ? 'Service Type, ' : '');
                $configEmptyFileds .= (empty($postalCode) ? 'Postal Code, ' : '');
                $configEmptyFileds .= (empty($shippingMethods) ? 'Domestic Shipping Services' : '');
                $content = [
                    'type' => 'error',
                    'data' => 'Please fill the configuration section. Empty Configurations are ' . $configEmptyFileds
                ];
                return $content;
            }

            $paymentMethods = explode(',', $paymentMethods);

            //image select
            
            $allImg = $product->getMediaGallery('images');
            foreach ($allImg as $value) {
                $pictureUrls[] = $this->objectManager->get('Magento\Catalog\Model\Product\Media\Config')->getMediaUrl($value['file']);
            }
            if (empty($pictureUrls)) {
                $content = [
                    'type' => 'error',
                    'data' => 'Product SKU:'.$product->getSku().' product images not found'
                ];
                return $content;
            }
            //categorry select
            $catJson = $profileData->getProfileCategory();
            $primarycatId = "";
            if ($catJson) {
                $catArray = array_reverse(json_decode($catJson, true));
                foreach ($catArray as $value) {
                    if ($value != "") {
                        $primarycatId = $value;
                        break;
                    }
                }
            }

            $configAttrlist = $profileData->getProfileCatAttribute();
            $catSpecifics = json_decode($configAttrlist, true);
            $nameValueList = "";
            if (!empty($catSpecifics['required_attributes'])) {
                foreach ($catSpecifics['required_attributes'] as $reqAttr) {
                    $catValue = $reqAttr['magento_attribute_code'] =='default' ? $reqAttr['default'] : $this->getMagentoProductAttributeValue($product, $reqAttr['magento_attribute_code']);/*$product->getData($reqAttr['magento_attribute_code']);*/
                    if (empty($catValue)) {
                        $content = [
                            'type' => 'error',
                            'data' => 'Product SKU:'.$product->getSku()." please fill " . $reqAttr['magento_attribute_code'] . " attribute's value"
                        ];
                        return $content;
                    }
                    if ($reqAttr['magento_attribute_code'] == 'country_of_manufacture') {
                        $catValue = $this->_country->create()->loadByCode($catValue)->getName();
                    }
                    $nameValueList .= '<NameValueList>
                                      <Name>' . $reqAttr['ebaymultiaccount_attribute_name'] . '</Name>
                                      <Value>' . $catValue . '</Value>
                                    </NameValueList>';
                }
                
            }
            if (!empty($catSpecifics['optional_attributes'])) {
                foreach ($catSpecifics['optional_attributes'] as $optAttr) {
                    $catValue = $optAttr['magento_attribute_code'] =='default' ? $optAttr['default'] : $this->getMagentoProductAttributeValue($product, $optAttr['magento_attribute_code']);/*$product->getData($optAttr['magento_attribute_code']);*/
                    if ($optAttr['magento_attribute_code'] == 'country_of_manufacture') {
                        $catValue = $this->_country->create()->loadByCode($catValue)->getName();
                    }
                    if (!empty($catValue)) {
                        $nameValueList .= '<NameValueList>
                                      <Name>' . $optAttr['ebaymultiaccount_attribute_name'] . '</Name>
                                      <Value>' . $catValue . '</Value>
                                    </NameValueList>';
                    }
                }            
            }

            $reqOptAttr = $profileData->getProfileReqOptAttribute();
            $item = $this->reqOptAttributeData($product, json_decode($reqOptAttr, true));
            if (isset($item['type'])) {
                $content = [
                    'type' => 'error',
                    'data' => 'Product SKU:'.$product->getSku().$item['data']
                ];
                return $content;
            }
            $conditionID = $profileData->getProfileCatFeature();
            if ($nameValueList != "" && $nameValueList != null) {
                $nameValueList = '<ItemSpecifics>' . $nameValueList . '</ItemSpecifics>';
                $item['ItemSpecifics'] = "ced";
            }
            if ($product->getTypeId() == 'configurable') {
                $configResponse = $this->createConfigProduct($product);
                $item['Variations'] = /*$configResponse*/'config_specifics';
            }
            if ($maxQty) {
                $item['QuantityRestrictionPerBuyer']['MaximumQuantity'] = $maxQty;
            }
                
            $item['PrimaryCategory']['CategoryID'] = $primarycatId;
            $item['StartPrice'] = $this->getEbayMultiAccountPrice($product);
            $item['CategoryMappingAllowed'] = true;
            if ($conditionID) {
                $item['ConditionID'] = $conditionID;            
            }

            $item['ShippingDetails'] = 'cedShippingDetails';
            $excludedAreaArray = !empty($excludedArea) ? explode(',', $excludedArea) : [];
            $excludedArea = '';
            foreach ($excludedAreaArray as $excludedLocation) {
                $excludedArea .= '<ExcludeShipToLocation>'.$excludedLocation.'</ExcludeShipToLocation>';
            }
            $shippingType = '<ShippingType>'.$serviceType.'</ShippingType>
                            <GlobalShipping>'.$globalShipping.'</GlobalShipping>'.$excludedArea;

            $internationalshipServices = '';
            if ($globalShipping && !empty($internationalShipping)) {
                if (is_string($internationalShipping) && strpos($internationalShipping, 's:') !== false) {
                    $internationalShippingDetails = unserialize($internationalShipping);
                } else {
                    $internationalShippingDetails = $internationalShipping;
                    //$internationalShippingDetails = json_decode($internationalShipping, true);
                }
                $shipToLocationArray = !empty($shipToLocation) ? explode(',', $shipToLocation) : [];
                $shipToLocation = '';
                foreach ($shipToLocationArray as $location) {
                    $shipToLocation .= '<ShipToLocation>'.$location.'</ShipToLocation>';
                }
                $count = 1;
                foreach ($internationalShippingDetails as $value) {
                    $internationalshipServices .= '<InternationalShippingServiceOption>
                                    <ShippingService>'.$value['service'].'</ShippingService>
                                    <ShippingServiceCost>'.$value['charge'].'</ShippingServiceCost>
                                    <ShippingServiceAdditionalCost>'.$value['add_charge'].'</ShippingServiceAdditionalCost>
                                    <ShippingServicePriority>' . $count . '</ShippingServicePriority>
                                    '.$shipToLocation.'
                                    </InternationalShippingServiceOption>';
                                    $count++;
                }

            }
            if (!empty($salesTaxState) && !empty($salesTaxRate)) {
                $shippingType .= '<SalesTax>
                                    <SalesTaxPercent>'.$salesTaxRate.'</SalesTaxPercent>
                                    <SalesTaxState>'.$salesTaxState.'</SalesTaxState>
                                    <ShippingIncludedInTax>'.$shippingIncludes.'</ShippingIncludedInTax>
                                </SalesTax>';

                /*$shippingType .= '<SalesTax>
                                    <SalesTaxPercent>'.$salesTaxRate.'</SalesTaxPercent>
                                    <ShippingIncludedInTax>'.$shippingIncludes.'</ShippingIncludedInTax>
                                </SalesTax>';*/
            }


            if (!empty($shippingMethods)) {
                if (is_string($shippingMethods) && strpos($shippingMethods, 's:') !== false) {
                    $shippingDetails = unserialize($shippingMethods);
                } else {
                    $shippingDetails = $shippingMethods;
                    //$shippingDetails = json_decode($shippingMethods, true);
                }
                $shipServices = '';
                $count = 1;
                foreach ($shippingDetails as $value) {
                    $shipServices .= '<ShippingServiceOptions>
                                    <FreeShipping>' . $freeShipping . '</FreeShipping>
                                    <ShippingService>'.$value['service'].'</ShippingService>
                                    <ShippingServiceCost>'.$value['charge'].'</ShippingServiceCost>
                                    <ShippingServiceAdditionalCost>'.$value['add_charge'].'</ShippingServiceAdditionalCost>
                                    <ShippingServicePriority>' . $count . '</ShippingServicePriority>
                                    </ShippingServiceOptions>';
                                    $count++;
                }
            }

            $discountPolicy = '<ShippingDiscountProfileID>256790026</ShippingDiscountProfileID>';
            $finalShipService = $shippingType.$shipServices.$internationalshipServices.$discountPolicy;
            
            $item['ReturnPolicy']['ReturnsAcceptedOption'] = $returnAccepted;
            if ($returnAccepted == 'ReturnsAccepted') {
                if ($country == 'US') {
                    $item['ReturnPolicy']['RefundOption'] = $refundType;
                }
                //$returnDays = $returnDays == 'Days_14' ? 'Days_30' : $returnDays;
                $item['ReturnPolicy']['ReturnsWithinOption'] = $returnDays;
                $item['ReturnPolicy']['ShippingCostPaidByOption'] = $shipCostPaidby;
            }
            if (in_array($this->_siteID, [71,77,101,186])) {
                $item['ReturnPolicy']['Description'] = $returnDescription;
            }
            $item['HitCounter'] = 'HiddenStyle';

            $item['Site'] = $site;
            $item['Country'] = $country;        
            $item['Currency'] = $currency;
            $item['PostalCode'] = $postalCode;
            $item['VATDetails']['VATPercent'] = 20.00;
            $item['OutOfStockControl'] = true;
            if ($itemLocation) {
                $item['Location'] = $itemLocation;
            }

            $item['PaymentMethods'] = 'ced';
            $item['PictureDetails']['PictureURL'] = 'cedPicture';
            $paymethod = '';
            $allImgs = '';
            foreach ($pictureUrls as $pictureUrl) {
                $allImgs .= '<PictureURL>' . $pictureUrl . '</PictureURL>';
            }
            foreach ($paymentMethods as $paymentmethod) {
                $paymethod .= '<PaymentMethods>' . $paymentmethod . '</PaymentMethods>';
            }
            if ($paypalEmail && in_array('PayPal', $paymentMethods)) {
                $item['PayPalEmailAddress'] = $paypalEmail;
            }
            $msgId = '';
            if ($type) {
                $xmlArray['AddItemRequestContainer']['MessageID'] = $product->getEntityId();
                $xmlArray['AddItemRequestContainer']['Item'] = $item;
                $rootElement = "AddItemRequestContainer";
                $xml = new \SimpleXMLElement("<$rootElement/>");
                $this->array2XML($xml, $xmlArray['AddItemRequestContainer']);
            } else {
                $msgId = "<MessageID>".$product->getEntityId()."</MessageID>";
                $xmlArray['Item'] = $item;
                $rootElement = "Item";
                $xml = new \SimpleXMLElement("<$rootElement/>");
                $this->array2XML($xml, $xmlArray['Item']);
            }

            $finalXml = $xml->asXML();
            if ($msgId) {
                $finalXml = $msgId.$finalXml;
            }
            if (strpos($finalXml, '<ItemSpecifics>ced</ItemSpecifics>') !== false) {
                $finalXml = str_replace('<ItemSpecifics>ced</ItemSpecifics>', $nameValueList, $finalXml);
            }
            if (strpos($finalXml, '<PictureURL>cedPicture</PictureURL>') !== false) {
                $finalXml = str_replace('<PictureURL>cedPicture</PictureURL>', $allImgs, $finalXml);
            }

            if (strpos($finalXml, '<ShippingDetails>cedShippingDetails</ShippingDetails>') !== false) {
                $finalXml = str_replace('cedShippingDetails', $finalShipService, $finalXml);
            }
            if (strpos($finalXml, '<PaymentMethods>ced</PaymentMethods>') !== false) {
                $finalXml = str_replace('<PaymentMethods>ced</PaymentMethods>', $paymethod, $finalXml);
            }
            if (strpos($finalXml, '<Variations>config_specifics</Variations>') !== false) {
                $finalXml = str_replace('<Variations>config_specifics</Variations>', $configResponse, $finalXml);
            }
            $content = [
                'type' => 'success',
                'data' => $finalXml
            ];
        } catch (\Exception $e) {
            $this->logger->addError('In Prepare Data: Product SKU:'.$product->getSku().' has exception '.$e->getMessage(), ['path' => __METHOD__]);
            $content = [
                'type' => 'error',
                'data' => 'Product SKU:'.$product->getSku().' has exception '.$e->getMessage()
            ];
        }
        return $content;
    }
    /**
     * @param $product
     * @param $reqOptAttr
     * @return array
     */
    public function reqOptAttributeData($product, $reqOptAttr)
    {
        try {
            $item = [];
            $account = $this->_coreRegistry->registry('ebay_account');
            $itemIdAttr = $this->multiAccountHelper->getItemIdAttrForAcc($account->getId());
            if ($product->getData($itemIdAttr) != '') {
                $item['ItemID'] = $product->getData($itemIdAttr);
            }
            $error = false;
            $msg = "";
            foreach ($reqOptAttr['required_attributes'] as $reqAttr) {
                switch ($reqAttr['ebaymultiaccount_attribute_name']) {
                    case 'name':
                        $item['Title'] = $reqAttr['magento_attribute_code'] =='default' ? $reqAttr['default'] : html_entity_decode(substr($product->getData($reqAttr['magento_attribute_code']), 0, 80));
                        
                        if (empty($item['Title'])) {
                            $error = true;
                            $msg = "Title is missing";
                        }
                        break;
                    case 'sku':
                        $item['SKU'] = $reqAttr['magento_attribute_code'] =='default' ? $reqAttr['default'] : $product->getData($reqAttr['magento_attribute_code']);
                        if (empty($item['SKU'])) {
                            $error = true;
                            $msg = "SKU is missing";
                        }
                        break;
                    
                    case 'description':
                        $item['Description'] = $reqAttr['magento_attribute_code'] =='default' ? $this->getDescriptionTemplate($product, $reqAttr['default']) : $product->getData($reqAttr['magento_attribute_code']);
                        if (empty($item['Description'])) {
                            $error = true;
                            $msg = "Description is missing";
                        }
                        break;
                    case 'inventory':
                        $quantity = $reqAttr['magento_attribute_code'] =='default' ? $reqAttr['default'] : $product->getData($reqAttr['magento_attribute_code']);
                        $item['Quantity'] = isset($quantity['qty']) ? $quantity['qty'] : $quantity;
                        if ($item['Quantity'] < 0) {
                            $stockItem = $this->objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
                            $stock = $stockItem->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
                            $qty = $stock->getQty();
                            $item['Quantity'] = $qty;
                        }
                        if ($item['Quantity'] < 0) {
                            $error = true;
                            $msg = "Quantity is missing";
                        }
                        
                    case 'max_dispatch_time':
                        $item['DispatchTimeMax'] = $reqAttr['magento_attribute_code'] =='default' ? $reqAttr['default'] : $product->getData($reqAttr['magento_attribute_code']);
                        if (empty($item['DispatchTimeMax']) && $reqAttr['magento_attribute_code'] =='default' && empty($reqAttr['default']) ) {

                        }elseif(empty($item['DispatchTimeMax'])){
                            $error = true;
                            $msg = "Dispatch Time Max is missing";
                        }
                        break;
                    case 'listing_type':
                        $item['ListingType'] = $reqAttr['magento_attribute_code'] =='default' ? $reqAttr['default'] : $product->getData($reqAttr['magento_attribute_code']);
                        if (empty($item['ListingType'])) {
                            $error = true;
                            $msg = "ListingType is missing";
                        }
                        break;
                    case 'listing_duration':
                        $item['ListingDuration'] = $reqAttr['magento_attribute_code'] =='default' ? $reqAttr['default'] : $product->getData($reqAttr['magento_attribute_code']);
                        if (empty($item['ListingDuration'])) {
                            $error = true;
                            $msg = "Listing Duration is missing";
                        }
                        break;
                    default:
                        break;
                }
                if ($error) {
                    break;
                }
            }
            if (!empty($reqOptAttr['optional_attributes'])) {
                foreach ($reqOptAttr['optional_attributes'] as $optAttr) {
                    switch ($optAttr['ebaymultiaccount_attribute_name']) {
                    case 'upc':
                        $item['ProductListingDetails']['UPC'] = $optAttr['magento_attribute_code'] =='default' ? $optAttr['default'] : $product->getData($optAttr['magento_attribute_code']);
                        break;
                    case 'ean':
                        $item['ProductListingDetails']['EAN'] = $optAttr['magento_attribute_code'] =='default' ? $optAttr['default'] : $product->getData($optAttr['magento_attribute_code']);
                        break;
                    case 'isbn':
                        $item['ProductListingDetails']['ISBN'] = $optAttr['magento_attribute_code'] =='default' ? $optAttr['default'] : $product->getData($optAttr['magento_attribute_code']);
                        break;
                    case 'brand':
                        $item['ProductListingDetails']['BrandMPN']['Brand'] = $optAttr['magento_attribute_code'] =='default' ? $optAttr['default'] : $product->getData($optAttr['magento_attribute_code']);
                        break;
                    case 'manufacturer_part_number':
                        $item['ProductListingDetails']['BrandMPN']['MPN'] = $optAttr['magento_attribute_code'] =='default' ? $optAttr['default'] : $product->getData($optAttr['magento_attribute_code']);
                        break;
                    case 'auto_pay':
                        $item['AutoPay'] = $optAttr['magento_attribute_code'] =='default' ? $optAttr['default'] : $product->getData($optAttr['magento_attribute_code']);
                        break;
                        default:
                            break;
                    }
                }
            }
            if (isset($item['ProductListingDetails']['BrandMPN']['Brand']) && !isset($item['ProductListingDetails']['BrandMPN']['MPN'])) {
                $item['ProductListingDetails']['BrandMPN']['MPN'] = "Does Not Apply";
            }
            if ($error) {
                $item['type'] = "error";
                $item['data'] = $msg;
            }
        } catch (\Exception $e) {
            $this->logger->addError('In Prepare Data: Product SKU:'.$product->getSku().' has exception '.$e->getMessage(), ['path' => __METHOD__]);
            $item['type'] = "error";
            $item['data'] = 'Product SKU:'.$product->getSku().' has exception '.$e->getMessage();
        }
        return $item;
    }

    /**
     * @param $product
     * @return string
     */
    public function createCustomOption($product)
    {
        $stockItem = $this->objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
        $stock = $stockItem->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $valuelist ='';
        $customOptions = $this->objectManager->get('Magento\Catalog\Model\Product\Option')->getProductOptionCollection($product);
        foreach($customOptions as $option) {
            $values = '';
            $varSpecificsSet = '<NameValueList>
                                  <Name>'.$option->getTitle().'</Name>';
            $optionval = $option->getValues();
            foreach($optionval as $value) {
                $values .= '<Value>'.$value->getTitle().'</Value>';
            }
            $valuelist .= $varSpecificsSet.
                        $values.
                    '</NameValueList>';
        }
        $varSpecSetArray = !empty($valuelist) ? '<VariationSpecificsSet>'.$valuelist.'</VariationSpecificsSet>' : '';
        $variations = '';
        foreach($customOptions as $option) {
            $optionval = $option->getValues();
            foreach($optionval as $value) {
                $price = floatval($value->getPrice()) + floatval($this->getEbayMultiAccountPrice($product));
                $variations .=   '<Variation>
                                <Quantity>'.$stock->getQty().'</Quantity>
                                <VariationSpecifics>
                                    <NameValueList>
                                        <Name>'.$option->getTitle().$value->getOptionTypeId().'</Name>
                                        <Value>'.$value->getTitle().'</Value>
                                    </NameValueList>
                                </VariationSpecifics>
                            </Variation>';
            }
        }
        $finalXml = !empty($variations) ? '<Variations>'.$varSpecSetArray.$variations.'</Variations>' : '';
        return $finalXml;
    }
    
    /**
     * @param $product
     * @return string
     */
    public function createConfigProduct($product)
    {
        $modifySpecificsName = $valuelist ='';
        $productType = $product->getTypeInstance();
        $attrs = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
        foreach ($attrs as $attr) {
            $values = '';
            $varSpecificsSet = '<NameValueList>
                                  <Name>'.$attr['label'].'</Name>';
            foreach ($attr['values'] as $attrValues) {
                $values .= '<Value>'.$attrValues['label'].'</Value>';
            }
            $valuelist .= $varSpecificsSet.
                                $values.
                        '</NameValueList>';
        }
        $varSpecSetArray = !empty($valuelist) ? '<VariationSpecificsSet>'.$valuelist.'</VariationSpecificsSet>' : '';
        $variations = '';
        $configProd = $productType->getUsedProducts($product);
        foreach ($configProd as $childprod) {
            $nameValList = '';
            $stockItem = $this->objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
            $stock = $stockItem->getStockItem($childprod->getId(), $childprod->getStore()->getWebsiteId());
            $prepareVar = '<Variation>
                            <SKU>'.$childprod->getSku().'</SKU>
                            <StartPrice>'.$this->getEbayMultiAccountPrice($product).'</StartPrice>
                            <Quantity>'.$stock->getQty().'</Quantity>
                            <VariationSpecifics>';
            $attrs = $product->getTypeInstance(true)->getConfigurableAttributes($product);
            foreach ($attrs as $attr) {
                $lable = $attr->getLabel();
                $value = $this->getMagentoProductAttributeValue($childprod, $attr->getProductAttribute()->getAttributeCode());
                $nameValList .= '<NameValueList>
                                <Name>'.$lable.'</Name>
                                <Value>'.$value.'</Value>
                                </NameValueList>';
            }
            $variation = $prepareVar.$nameValList.
                            '</VariationSpecifics>
                        </Variation>';
            $variations .= $variation;
        }
        $finalXml = !empty($variations) ? '<Variations>'.$modifySpecificsName.$varSpecSetArray.$variations.'</Variations>' : '';
        return $finalXml;
    }

    /**
     * @param $productId
     * @return array
     */
    public function getInventoryPrice($productId)
    {
        try {
            $accountId = 0;
            $account = $this->_coreRegistry->registry('ebay_account');
            if($account) {
                $accountId = $account->getId();
            }
            $itemIdAccAttr = $this->multiAccountHelper->getItemIdAttrForAcc($accountId);
            $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($productId);
            $ebaymultiaccountItemId = $product->getData($itemIdAccAttr);
            if(!$ebaymultiaccountItemId) {
                $productParents = $this->objectManager
                    ->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
                    ->getParentIdsByChild($product->getId());
                foreach ($productParents as $parentId) {
                    $parentProduct = $this->objectManager->create('Magento\Catalog\Model\Product')->load($parentId);
                    $ebaymultiaccountItemId = $parentProduct->getData($itemIdAccAttr);
                    if($ebaymultiaccountItemId != null) {
                        break;
                    }
                }
            }
            $price = $this->getEbayMultiAccountPrice($product);
            $sku = $product->getSku();
            $stockItem = $this->objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
            $stock = $stockItem->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
            $qty = $stock->getQty();
            if (!empty($price) && !empty($ebaymultiaccountItemId) && !empty($sku) && $product->getTypeId() == 'simple') {
                $finalXml = '<InventoryStatus>
                                <SKU>' . $sku . '</SKU>
                                <ItemID>' . $ebaymultiaccountItemId . '</ItemID>
                                <StartPrice>' . $price . '</StartPrice>
                                <Quantity>' . $qty . '</Quantity>
                              </InventoryStatus>';

                $content = [
                    'type' => 'success',
                    'data' => $finalXml
                ];
            } else {
                $content = [
                    'type' => 'error',
                    'data' => 'please check Price or Inventory for: ' . $product->getSku()
                ];
            }
        } catch (\Exception $e) {
            $this->logger->addError('In Inventory Sync: has exception '.$e->getMessage(), ['path' => __METHOD__]);
            $content = [
                'type' => 'error',
                'data' => $e->getMessage()
            ];
        }
        return $content;
    }

    /**
     * @param $product
     * @return array
     */
    public function endListing($product, $type=null)
    {
        try {
            $accountId = 0;
            $account = $this->_coreRegistry->registry('ebay_account');
            if($account) {
                $accountId = $account->getId();
            }
            $itemIdAccAttr = $this->multiAccountHelper->getItemIdAttrForAcc($accountId);
            if (is_string($product)) {
                $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($product);
            }
            $ebaymultiaccountItemId = $product->getData($itemIdAccAttr);
            $listingType = $product->getListingType();
            $productId = $product->getEntityId();
            $message = $this->scopeConfig->getValue('ebaymultiaccount_config/product_upload/end_listing_reason');
            if ($message != '') {
                if ($listingType == 'Chinese') {
                    $message = 'SellToHighBidder';
                }
                if ($type) {
                    $finalXml = '<EndItemRequestContainer>
                                    <MessageID>' . $productId . '</MessageID>
                                    <EndingReason>' . $message . '</EndingReason>
                                    <ItemID>' . $ebaymultiaccountItemId . '</ItemID>
                                    </EndItemRequestContainer>';
                } else {
                    $finalXml = '<MessageID>' . $productId . '</MessageID>
                                <EndingReason>' . $message . '</EndingReason>
                                <ItemID>' . $ebaymultiaccountItemId . '</ItemID>';
                }

                $content = [
                    'type' => 'success',
                    'data' => $finalXml
                ];
            } else {
                $content = [
                    'type' => 'error',
                    'data' => "please fill the end listing reason in configuration"
                ];
            }
        } catch (\Exception $e) {
            $this->logger->addError('In End Listing: has exception '.$e->getMessage(), ['path' => __METHOD__]);
            $content = [
                'type' => 'error',
                'data' => $e->getMessage()
            ];
        }
        return $content;
    }

    /**
     * @param $product
     * @return float|null
     */

    public function getEbayMultiAccountPrice($product)
    {
        $price = (float)$product->getFinalPrice();
        if($product->getEbayPrice() && $product->getEbayPrice() > 0) {
            $price = (float)$product->getEbayPrice();
        } elseif($product->getSpecialPrice() && $product->getSpecialPrice() > 0) {
            $price = (float)$product->getSpecialPrice();
        } else {
            $price = (float)$product->getFinalPrice();
        }
        $price = $this->getConvertedPrice($price);

        $configPrice = trim(
            $this->scopeConfig->getvalue(
                'ebaymultiaccount_config/product_upload/product_price'
            )
        );

        switch ($configPrice) {
        case 'plus_fixed':
            $fixedPrice = trim(
                $this->scopeConfig->getvalue(
                    'ebaymultiaccount_config/product_upload/fix_price'
                )
            );
            $price = $this->forFixPrice($price, $fixedPrice, 'plus_fixed');
            break;

        case 'plus_per':
            $percentPrice = trim(
                $this->scopeConfig->getvalue(
                    'ebaymultiaccount_config/product_upload/percentage_price'
                )
            );
            $price = $this->forPerPrice($price, $percentPrice, 'plus_per');
            break;

        case 'min_fixed':
            $fixedPrice = trim(
                $this->scopeConfig->getvalue(
                    'ebaymultiaccount_config/product_upload/fix_price'
                )
            );
            $price = $this->forFixPrice($price, $fixedPrice, 'min_fixed');
            break;

        case 'min_per':
            $percentPrice = trim(
                $this->scopeConfig->getvalue(
                    'ebaymultiaccount_config/product_upload/percentage_price'
                )
            );
            $price = $this->forPerPrice($price, $percentPrice, 'min_per');
            break;

        case 'differ':
            $customPriceAttr = trim(
                $this->scopeConfig->getvalue(
                    'ebaymultiaccount_config/product_upload/different_price'
                )
            );
            try {
                $cprice = (float)$product->getData($customPriceAttr);
            } catch (\Exception $e) {
                $this->getResponse()->setBody($e->getMessage());
            }
            $price = ($cprice != 0) ? $cprice : $price;
            break;

        default:
            return (float)$price;

        }
        return $price;
    }

    /**
     * @param null $price
     * @param null $fixedPrice
     * @param $configPrice
     * @return float|null
     */
    public function forFixPrice($price = null, $fixedPrice = null, $configPrice)
    {
        if (is_numeric($fixedPrice) && ($fixedPrice != '')) {
            $fixedPrice = (float)$fixedPrice;
            if ($fixedPrice > 0) {
                $price = $configPrice == 'plus_fixed' ? (float)($price + $fixedPrice)
                    : (float)($price - $fixedPrice);
            }
        }
        return $price;
    }

    /**
     * @param null $price
     * @param null $percentPrice
     * @param $configPrice
     * @return float|null
     */
    public function forPerPrice($price = null, $percentPrice = null, $configPrice)
    {
        if (is_numeric($percentPrice)) {
            $percentPrice = (float)$percentPrice;
            if ($percentPrice > 0) {
                $price = $configPrice == 'plus_per' ?
                    (float)($price + (($price / 100) * $percentPrice))
                    : (float)($price - (($price / 100) * $percentPrice));
            }
        }
        return $price;
    }

    public function getQuantityForUpload($product)
    {
        $quantity = 0;
        if($product != null) {
            $profileId = $product->getEbayMultiAccountProfileId();
            $profileData = $this->objectManager->get('Ced\EbayMultiAccount\Model\Profile')->load($profileId);
            if($profileData->getId() == null) {
                $productParents = $this->objectManager
                        ->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
                        ->getParentIdsByChild($product->getId());
                foreach ($productParents as $parentId) {
                    $parentProduct = $this->objectManager->create('Magento\Catalog\Model\Product')->load($parentId);
                    $profileId = $parentProduct->getEbayMultiAccountProfileId();
                    if($profileId != null) {
                        $profileData = $this->objectManager->get('Ced\EbayMultiAccount\Model\Profile')->load($profileId);
                        if($profileData->getId() > 0) {
                            break;
                        }
                    }
                }
            }
            if($profileData->getId() > 0) {
                $reqOptAttr = $profileData->getProfileReqOptAttribute();
                $attributes = json_decode($reqOptAttr, true);
                $attributes = isset($reqOptAttr['required_attributes']) ? array_column($reqOptAttr['required_attributes'], 'ebaymultiaccount_attribute_name'): [];

                if (is_array($attributes) && isset($attributes['inventory'])) {
                    $quantity = ($attributes['inventory']['magento_attribute_code'] == "default") ? $attributes['inventory']['default'] : $product->getData($attributes['inventory']['magento_attribute_code']);
                    $quantity = isset($quantity['qty']) ? (int)$quantity['qty'] : (int)$quantity;
                } else {
                    $stockItem = $this->objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
                    $stock = $stockItem->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
                    $quantity = (int)$stock->getQty();
                }
            } else {
                $stockItem = $this->objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
                $stock = $stockItem->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
                $quantity = (int)$stock->getQty();
            }
        }
        return $quantity;
    }
    
    public function array2XML($xml_obj, $array)
    {
        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $key = $key;
            }
            if (is_array($value)) {
                $node = $xml_obj->addChild($key);
                $this->array2XML($node, $value);
            } else {
                $xml_obj->addChild($key, htmlspecialchars($value));
            }
        }
    }

    public function getDescriptionTemplate($product, $value=null)
    {
        preg_match_all("/\##(.*?)\##/", $value, $matches);
        foreach (array_unique($matches[1]) as $attrId) {
            $attrValue = $product->getData($attrId);
            $value = str_replace('##'.$attrId.'##', $attrValue, $value);
        }
       $description =$value;
        return $description;
    }

    public function prepareHeader($value)
    {
        $xmlHeader = '<?xml version="1.0" encoding="utf-8"?>';
        switch ($value) {
            case 'AddItem':
                $xmlHeader .= '
                            <AddItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                break;
            case 'AddFixedPriceItem':
                $xmlHeader .= '
                            <AddFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                break;
            case 'ReviseItem':
                $xmlHeader .= '
                            <ReviseItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                break;
            case 'ReviseFixedPriceItem':
                $xmlHeader .= '
                            <ReviseFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                break;
            case 'AddItems':
                $xmlHeader .= '
                            <AddItemsRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                break;
            case 'RelistItem':
                $xmlHeader .= '
                            <RelistItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                break;
            case 'EndItem':
                $xmlHeader .= '
                            <EndItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                break;
            case 'EndItems':
                $xmlHeader .= '
                            <EndItemsRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                break;
            case 'ReviseInventoryStatus':
                $xmlHeader .= '
                            <ReviseInventoryStatusRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                break;
            default:
                break;

        }
        $xmlHeader .= '
                                    <RequesterCredentials>
                                        <eBayAuthToken>' . $this->token . '</eBayAuthToken>
                                    </RequesterCredentials>
                                    <Version>989</Version>
                                    <ErrorLanguage>en_US</ErrorLanguage>
                                    <WarningLevel>High</WarningLevel>';

        return $xmlHeader;
    }

    public function getConfigData($fieldToRetrieve, $profileConfig = null) {
        $configData = '';
        if($profileConfig) {
            $paymentDetails = $profileConfig->getPaymentDetails();
            $shippingDetails = $profileConfig->getShippingDetails();
            $returnPolicy = $profileConfig->getReturnPolicy();
            if($paymentDetails && $paymentDetails != null) {
                $paymentDetails = $this->json->jsonDecode($paymentDetails);
                if(isset($paymentDetails[$fieldToRetrieve]))
                    $configData = $paymentDetails[$fieldToRetrieve];
            }
            if($shippingDetails && $shippingDetails != null) {
                $shippingDetails = $this->json->jsonDecode($shippingDetails);
                if(isset($shippingDetails[$fieldToRetrieve]))
                    $configData = $shippingDetails[$fieldToRetrieve];
            }
            if($returnPolicy && $returnPolicy != null) {
                $returnPolicy = $this->json->jsonDecode($returnPolicy);
                if(isset($returnPolicy[$fieldToRetrieve]))
                    $configData = $returnPolicy[$fieldToRetrieve];
            }
        }
        return $configData;
    }

    public function getMagentoProductAttributeValue($product, $attributeCode)
    {
        if ($product->getData($attributeCode) == "") {
            return $product->getData($attributeCode);
        }

        $attr = $product->getResource()->getAttribute($attributeCode);
        if ($attr && ($attr->usesSource() || $attr->getData('frontend_input') == 'select')) {
            $productAttributeValue = $attr->getSource()->getOptionText($product->getData($attributeCode));
        } else {
            $productAttributeValue = $product->getData($attributeCode);
        }
        return $productAttributeValue;
    }

    public function getConvertedPrice($price) {
        $convertCurrency = trim(
            $this->scopeConfig->getvalue(
                'ebaymultiaccount_config/product_upload/convert_curreny'
            )
        );

        if($convertCurrency) {
            $countryDetails = $this->objectManager->get('Ced\EbayMultiAccount\Helper\Data')->getEbayMultiAccountsites($this->_siteID);
            $toCurrency = isset($countryDetails['currency'][0]) ? $countryDetails['currency'][0] : '';
            $currency =  $this->objectManager->get('Magento\Directory\Model\CurrencyFactory');
            $currencyRate = $currency->create()->load($this->_storeManager->getStore()->getBaseCurrency()->getCode())->getAnyRate($toCurrency);
            if($currencyRate)
                $price = $this->_storeManager->getStore()->getBaseCurrency()->convert($price, $toCurrency);
        }

        return $price;
    }
}