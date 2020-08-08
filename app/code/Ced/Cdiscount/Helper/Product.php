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
 * @package     Ced_Cdiscount
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Helper;

/**
 * Directory separator shorthand
 */


use Ced\Cdiscount\Model\ResourceModel\CdiscountAttributes\CollectionFactory;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * Class Data For Cdiscount Authenticated Seller Api
 * @package Ced\Cdiscount\Helper
 */
class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ATTRIBUTE_TYPE_SKU = 'sku';

    const ATTRIBUTE_TYPE_NORMAL = 'normal';

    const CDISCOUNT_DIRECTORY = 'cdiscount';

    const CDISCOUNT_PRODUCT_STATUS = 'cdiscount_product_status';

    const MAGENTO_ATTRIBUTE_CODE = 'magento_attribute_code';

    const REQUIRED_ATTRIBUTE = [
        \Ced\Cdiscount\Helper\Category::ATTRIBUTE_SHORT_LABEL,
        \Ced\Cdiscount\Helper\Category::ATTRIBUTE_LONG_LABEL,
        \Ced\Cdiscount\Helper\Category::ATTRIBUTE_DESCRIPTION,
        \Ced\Cdiscount\Helper\Category::ATTRIBUTE_BRAND_NAME
    ];

    const SKIPPED_ATTRIBUTES = [
        \Ced\Cdiscount\Helper\Category::ATTRIBUTE_SHORT_LABEL,
        \Ced\Cdiscount\Helper\Category::ATTRIBUTE_LONG_LABEL,
        \Ced\Cdiscount\Helper\Category::ATTRIBUTE_DESCRIPTION,
        \Ced\Cdiscount\Helper\Category::ATTRIBUTE_COMMENT,
        \Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN,
        \Ced\Cdiscount\Helper\Category::ATTRIBUTE_SELLER_PRODUCT_ID,
        \Ced\Cdiscount\Helper\Category::ATTRIBUTE_SALE_PRICE
    ];

    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    public $shippingDetails;

    public $_requiredAttribute = false;

    /**
     * Json Parser
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $json;

    /**
     * Xml Parser
     * @var \Magento\Framework\Convert\Xml
     */
    public $xml;

    /**
     * DirectoryList
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    public $directoryList;

    /**
     * Date/Time
     * @var $dateTime
     */
    public $dateTime;

    public $packageName;

    /**
     * File Manager
     * @var $fileIo
     */
    public $fileIo;

    /**
     * Cdiscount Logger
     * @var \Ced\Cdiscount\Helper\Logger
     */
    public $logger;

    /**
     * @var Profile
     */
    public $profileHelper;

    /**
     * Selected Store Id
     * @var $selectedStore
     */
    public $selectedStore;

    /**
     * Api
     * @var $api
     */
    public $config;

    /**
     * @var mixed
     */
    public $registry;

    /**
     * Config Manager
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfigManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * Feeds Model
     * @var \Ced\Cdiscount\Model\FeedsFactory
     */
    public $feeds;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $product;

    public $collectionFactory;
    /**
     * @var string
     */
    public $apiAuthKey;
    public $urlBuilder;
    public $fulfillmentLagTime;
    public $ids = [];
    public $data = [];
    public $offer = [];
    public $key = 0;
    public $mpn = '';
    public $attributeCollectionFactory;
    public $cdiscount;
    public $imageHelper;
    public $stockState;
    public $debugMode;
    public $generator;
    public $zip;
    public $apiClient;
    public $barcode;
    public $identifire;
    public $offerPath;
    public $path;
    public $categoriesFactory;
    public $htmlToText;

    /**
     * Product constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Json\Helper\Data $json
     * @param \Magento\Framework\Xml\Generator $generator
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Filesystem\Io\File $fileIo
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\Message\ManagerInterface $manager
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     * @param \Sdk\Product\ProductFactory $cdiscount
     * @param \Ced\Cdiscount\Model\FeedsFactory $feedsFactory
     * @param Config $config
     * @param Logger $logger
     * @param Profile $profile
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Framework\Xml\Generator $generator,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $fileIo,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $manager,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\Archive\Zip $zipArchive,
        \Magento\Catalog\Helper\ImageFactory $imageHelper,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Sdk\Product\ProductFactory $cdiscount,
        \Sdk\ApiClient\CDSApiClientFactory $apiClient,
        \Sdk\Product\IdentifierRequest $identifierRequest,
        \Ced\Cdiscount\Model\FeedsFactory $feedsFactory,
        \Ced\Cdiscount\Model\ResourceModel\Category\CollectionFactory $categoriesFactory,
        \Ced\Cdiscount\Helper\BarcodeValidator $barcodeValidator,
        \Ced\Cdiscount\Helper\Config $config,
        \Ced\Cdiscount\Helper\Logger $logger,
        \Ced\Cdiscount\Helper\Profile $profile,
        \Ced\Cdiscount\Model\ResourceModel\CdiscountAttributes\CollectionFactory $attributeCollectionFactory,
        \Ced\Cdiscount\Model\Xml\GeneratorFactory $generatorFactory,
        \Ced\Cdiscount\Helper\Html2TextFactory $html2TextFactory
    ) {
        parent::__construct($context);
        $this->objectManager = $objectManager;
        $this->collectionFactory = $collectionFactory;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->json = $json;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->xml = $generator;
        $this->directoryList = $directoryList;
        $this->fileIo = $fileIo;
        $this->dateTime = $dateTime;
        $this->scopeConfigManager = $context->getScopeConfig();
        $this->messageManager = $manager;
        $this->registry = $registry;
        $this->product = $product;
        $this->stockState = $stockState;
        $this->logger = $logger;
        $this->cdiscount = $cdiscount;
        $this->profileHelper = $profile;
        $this->feeds = $feedsFactory;
        $this->config = $config;
        $this->imageHelper = $imageHelper;
        $this->selectedStore = $config->getStore();
        $this->generator = $generatorFactory;
        $this->zip = $zipArchive;
        $this->apiClient = $apiClient;
        $this->barcode = $barcodeValidator;
        $this->identifire = $identifierRequest;
        $this->categoriesFactory = $categoriesFactory;
        $this->debugMode = $config->getDebugMode();
        $this->htmlToText = $html2TextFactory;
    }

    /**
     * @param $path
     * @param string $code
     * @return bool|mixed|string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function loadFile($path, $code = '')
    {

        if (!empty($code)) {
            $path = $this->directoryList->getPath($code) . "/" . $path;
        }

        if ($this->fileIo->fileExists($path)) {
            $pathInfo = pathinfo($path);
            if ($pathInfo['extension'] == 'json') {
                $myfile = fopen($path, "r");
                $data = fread($myfile, filesize($path));
                fclose($myfile);
                if (!empty($data)) {
                    try {
                        $data = $this->json->jsonDecode($data);
                        return $data;
                    } catch (\Exception $e) {
                        if ($this->debugMode == true) {
                            $this->logger->debug($e->getMessage(), ['path' => __METHOD__]);
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param array $ids
     * @return bool
     * @throws \Exception
     */
    public function getUploadedProductStatus($ids = [])
    {
        try {
            $response = false;
            if (isset($ids) and !empty($ids)) {
                $uploadedEans = [];
                $relation = [];
                $eans = [];
                $userName = $this->config->getUserName();
                $password = $this->config->getUserPassword();
                $client = $this->apiClient->create(['username' => $userName, 'password' => $password]);
                $token = $client->init();
                if (empty($token)) {
                    return false;
                }
                $productPoint = $client->getProductPoint();
                $identifire = $this->identifire;
                $identifire->setIdentifierType(\Sdk\Product\IdentifierTypeEnum::EAN);
                foreach ($ids as $id) {
                    $products = $this->product->create()->loadByAttribute('entity_id', $id);
                    $profileId = $products->getCdiscountProfileId();
                    if ($products->getTypeId() == 'configurable' &&
                        $products->getVisibility() != 1) {
                        $configurableProduct = $products;
                        $childIds = $configurableProduct->getTypeInstance()->getChildrenIds($products->getId());
                        // getting child products
                        $childs = $this->collectionFactory->create()
                            ->addAttributeToSelect('*')
                            ->addAttributeToFilter('entity_id', ['in' => $childIds]);
                        foreach ($childs as $product) {
                            $mappedAttributeForEan = $this->profileHelper->getProfile($id, $profileId)
                                ->getMappedAttribute(\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN);
                            if ($mappedAttributeForEan != false && !empty($product->getData($mappedAttributeForEan))) {
                                $ean = trim((string)$product->getData($mappedAttributeForEan));
                                $eans[$products->getId()][$ean] = $ean;
                                $relation[$product->getId()] = $products->getId();
                                $identifire->addValue($ean);
                            }
                        }
                    } elseif ($products->getTypeId() == 'simple' &&
                        $products->getVisibility() != 1) {
                        $mappedAttributeForEan = $this->profileHelper->getProfile($id, $profileId)
                            ->getMappedAttribute(\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN);
                        if ($mappedAttributeForEan != false && !empty($products->getData($mappedAttributeForEan))) {
                            $identifire->addValue(trim((string)$products->getData($mappedAttributeForEan)));
                        }
                    }
                }

                $getProductListByIdentifierResponse = $productPoint->getProductListByIdentifier($identifire);
                if (is_array($getProductListByIdentifierResponse->getProductList()) and
                    !empty($getProductListByIdentifierResponse->getProductList())) {
                    foreach ($getProductListByIdentifierResponse->getProductList() as $cdiscountProduct) {
                        if ($cdiscountProduct->getHasError() == 'true') {
                            continue;
                        }
                        $uploadedEans[] = $cdiscountProduct->getEAN();
                    }
                }
                if (isset($uploadedEans) and !empty($uploadedEans)) {
                    foreach ($uploadedEans as $uploadedEan) {
                        $liveProducts = $this->product->create()->loadByAttribute($mappedAttributeForEan, $uploadedEan);
                        if ($liveProducts) {
                            if (isset($relation[$liveProducts->getId()],
                                $eans[$relation[$liveProducts->getId()]],
                                $eans[$relation[$liveProducts->getId()]][$uploadedEan])) {
                                unset($eans[$relation[$liveProducts->getId()]][$uploadedEan]);
                                $parent = $this->product->create()
                                    ->loadByAttribute('entity_id', $relation[$liveProducts->getId()]);
                                if (count($eans[$relation[$liveProducts->getId()]]) == 0 && $parent) {
                                    $parent
                                        ->setData(self::CDISCOUNT_PRODUCT_STATUS,
                                            \Ced\Cdiscount\Model\Source\Product\Status::LIVE);
                                    $parent->getResource()
                                        ->saveAttribute($parent, self::CDISCOUNT_PRODUCT_STATUS);

                                } else {
                                    $parent
                                        ->setData(self::CDISCOUNT_PRODUCT_STATUS,
                                            \Ced\Cdiscount\Model\Source\Product\Status::PARTIALLY_LIVE);
                                    $parent
                                        ->getResource()
                                        ->saveAttribute($parent, self::CDISCOUNT_PRODUCT_STATUS);
                                }
                            }
                            $liveProducts
                                ->setData(self::CDISCOUNT_PRODUCT_STATUS,
                                    \Ced\Cdiscount\Model\Source\Product\Status::LIVE);
                            $liveProducts
                                ->getResource()
                                ->saveAttribute($liveProducts, self::CDISCOUNT_PRODUCT_STATUS);
                        }
                    }
                    $response = true;
                }
            }
        } catch (\Exception $e) {
            if ($this->debugMode == true) {
                $this->logger->error($e->getMessage(), ['path' => __METHOD__, 'product_ids' => $ids]);
            }
        }
        return $response;
    }


    public function getProducts($ids = [])
    {
        try{
            $response = [
                'success' => false,
                'data' => []
            ];
            if (isset($ids) && !empty($ids)) {
                $userName = $this->config->getUserName();
                $password = $this->config->getUserPassword();
                $client = $this->apiClient->create(['username' => $userName, 'password' => $password]);
                $token = $client->init();
                if (empty($token)) {
                    return $response;
                }
                $productPoint = $client->getProductPoint();
                $identifire = $this->identifire;
                $identifire->setIdentifierType(\Sdk\Product\IdentifierTypeEnum::EAN);
                foreach ($ids as $id) {
                    $products = $this->product->create()->loadByAttribute('entity_id', $id);
                    $profileId = $products->getCdiscountProfileId();
                    if ($products->getTypeId() == 'configurable' &&
                        $products->getVisibility() != 1) {
                        $configurableProduct = $products;
                        $childIds = $configurableProduct->getTypeInstance()->getChildrenIds($products->getId());
                        // getting child products
                        $childs = $this->collectionFactory->create()
                            ->addAttributeToSelect('*')
                            ->addAttributeToFilter('entity_id', ['in' => $childIds]);
                        foreach ($childs as $product) {
                            $mappedAttributeForEan = $this->profileHelper->getProfile($id, $profileId)
                                ->getMappedAttribute(\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN);
                            if ($mappedAttributeForEan != false && !empty($product->getData($mappedAttributeForEan))) {
                                $ean = trim((string)$product->getData($mappedAttributeForEan));
                                $identifire->addValue($ean);
                            }
                        }
                    } elseif ($products->getTypeId() == 'simple' &&
                        $products->getVisibility() != 1) {
                        $mappedAttributeForEan = $this->profileHelper->getProfile($id, $profileId)
                            ->getMappedAttribute(\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN);
                        if ($mappedAttributeForEan != false && !empty($products->getData($mappedAttributeForEan))) {
                            $identifire->addValue(trim((string)$products->getData($mappedAttributeForEan)));
                        }
                    }
                    $getProductListByIdentifierResponse = $productPoint->getProductListByIdentifier($identifire);
                    if ($getProductListByIdentifierResponse->hasError()) {
                        $response['data']['errors'][] = $getProductListByIdentifierResponse->getErrorMessage();
                    } else {
                        foreach ($getProductListByIdentifierResponse->getProductList() as $product) {
                            $response['success'] = true;
                            $response['data'][$product->getEAN()] = [
                                'EAN' => $product->getEAN(),
                                'Brand' => $product->getBrandName(),
                                'Categorie' => $product->getCategoryCode(),
                                'Name' =>  $product->getName(),
                                'SKU(parent)' => $product->getFatherProductId(),
                                'Product Image' => "<img src={$product->getImageURL()}>",
                                'Type Of Product' => $product->getProductType(),
                                'Color' => $product->getColor(),
                                'Size' => $product->getSize()
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            if ($this->debugMode == true) {
                $this->logger->error($exception->getMessage(), ['path' => __METHOD__, 'Product_ids' => $ids]);
            }
        }
        return $response;
    }

    /**
     * @param $data
     * @param array $params
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createFile($data, $params = [])
    {
        $type = 'json';
        $timestamp = $this->objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
        $name = 'cdiscount_' . $timestamp->gmtTimestamp();
        $path = 'cdiscount';
        $code = 'var';

        if (isset($params['type'])) {
            $type = $params['type'];
        }
        if (isset($params['name'])) {
            $name = $params['name'];
        }
        if (isset($params['path'])) {
            $path = $params['path'];
        }
        if (isset($params['code'])) {
            $code = $params['code'];
        }

        if ($type == 'xml') {
            $xmltoarray = $this->objectManager->create('Magento\Framework\Convert\ConvertArray');
            $data = $xmltoarray->assocToXml($data);
        } elseif ($type == 'json') {
            $data = $this->json->jsonEncode($data);
        } elseif ($type == 'string') {
            $data = ($data);
        }

        $dir = $this->createDir($path, $code);
        $filePath = $dir['path'];
        $fileName = $name . "." . $type;
        try {
            $this->fileIo->write($filePath . "/" . $fileName, $data);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param string $name
     * @param string $code
     * @return array|bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createDir($name = self::CDISCOUNT_DIRECTORY, $code = 'var')
    {
        $path = $this->directoryList->getPath($code) . "/" . $name;
        if ($this->fileIo->fileExists($path)) {
            return ['status' => true, 'path' => $path, 'action' => 'dir_exists'];
        } else {
            try {
                $this->fileIo->mkdir($path, 0775, true);
                return ['status' => true, 'path' => $path, 'action' => 'dir_created'];
            } catch (\Exception $e) {
                if ($this->debugMode == true) {
                    $this->logger->error($e->getMessage(), ['path' => __METHOD__, 'trace' => $e->getTraceAsString()]);
                }
                return false;
            }
        }
    }

    /**
     * @param array $ids
     * @return bool
     * @throws \Exception
     */
    public function createProducts($ids = [])
    {
        $response = false;

        try {
            $timestamp = $this->dateTime->gmtTimestamp();
            $ids = $this->validateAllProducts($ids);
            if (!empty($ids['simple']) or !empty($ids['configurable'])) {
                $capacity = count($ids['simple']);
                foreach ($ids['configurable'] as $simpleIds) {
                    $capacity += count($simpleIds);
                }

                $this->packageName = "PackageName{$timestamp}";

                $this->ids = [];
                $this->key = 0;
                $this->data = [
                    'ProductPackage' => [
                        '_attribute' => [
                            'Name' => "Package Name " . $timestamp,
                            'xmlns' => 'clr-namespace:Cdiscount.Service.ProductIntegration.Pivot;assembly=Cdiscount.Service.ProductIntegration',
                            'xmlns:x' => 'http://schemas.microsoft.com/winfx/2006/xaml'
                        ],
                        '_value' => [
                            'ProductPackage.Products' => [
                                '_attribute' => [],
                                '_value' => [
                                    'ProductCollection' => [
                                        '_attribute' => [
                                            'Capacity' => $capacity
                                        ],
                                        '_value' => []
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];

                $this->offer = [
                    'OfferPackage' => [
                        '_attribute' => [
                            'Name' => "Offer Package " . $timestamp,
                            'PurgeAndReplace' => 'False',
                            'PackageType' => 'Full',
                            'xmlns' => 'clr-namespace:Cdiscount.Service.OfferIntegration.Pivot;assembly=Cdiscount.Service.OfferIntegration',
                            'xmlns:x' => 'http://schemas.microsoft.com/winfx/2006/xaml',
                        ],
                        '_value' => [
                            'OfferPackage.Offers' => [
                                '_attribute' => [],
                                '_value' => [
                                    'OfferCollection' => [
                                        '_attribute' => [
                                            'Capacity' => $capacity
                                        ],
                                        '_value' => []
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
                $this->prepareSimpleProducts($ids['simple']);
                $this->prepareConfigurableProducts($ids['configurable']);
                $response = $this->createProductPackage($this->data);
//                print_r($this->generator->create()->arrayToXml($this->data)->__toString());die();
                if ($response == true) {
                    $this->updateStatus($this->ids, \Ced\Cdiscount\Model\Source\Product\Status::SUBMITTED);
                }
                $response = true;
            }
        } catch (\Exception $exception) {
            if ($this->config->getDebugMode() == true) {
                $this->logger->error($exception->getMessage(),
                    ['path' => __METHOD__, 'trace' => $exception->getTraceAsString()]);
            }
        }
        return $response;
    }

    /**
     * @param $data
     * @param $timestamp
     * @param $type
     * @return bool
     * @throws \DOMException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createRelation($data, $timestamp, $type)
    {
        $response = false;
        if (isset($data)) {
            $path = self::CDISCOUNT_DIRECTORY . DS . $type . $timestamp . DS . '_rels';
            $dir = $this->createDir($path);
            $name = '.rels';
            $path = $dir['path'] . DS . $name;
            $product = $this->generator->create()->arrayToXml($data);
            $product->save($path);
            $response = true;
        }
        return $response;
    }

    /**
     * @param $data
     * @param $timestamp
     * @param $type
     * @return bool
     * @throws \DOMException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createContentType($data, $timestamp, $type)
    {
        $response = false;
        if (isset($data)) {
            $path = self::CDISCOUNT_DIRECTORY . DS . $type . $timestamp;
            $name = '[Content_Types]' . '.xml';
            $dir = $this->createDir($path);
            $path = $dir['path'] . DS . $name;
            $product = $this->generator->create()->arrayToXml($data);
            $product->save($path);
            $response = true;
        }
        return $response;
    }

    /**
     * @param $data
     * @return bool
     */
    public function createProductPackage($data)
    {
        try {
            $response = false;
            if (isset($data)) {
                $timestamp = $this->dateTime->gmtTimestamp();

                // Saving Content Type
                $contentTypes = $this->prepareContentType();
                $this->createContentType($contentTypes, $timestamp, 'Product_');

                // Saving Product
                $path = self::CDISCOUNT_DIRECTORY . DS . 'Product_' . $timestamp . DS . 'Content';
                $dir = $this->createDir($path);
                $name = 'Products.xml';
                $path = $dir['path'] . DS . $name;
                $this->path = $path;
                $product = $this->generator->create()->arrayToXml($data);
                $product->save($path);

                // Saving Relations
                $relations = $this->prepareRelation($timestamp);
                $this->createRelation($relations, $timestamp, 'Product_');

                // Creating Zip File
                $zipDestination = $this->createDir(self::CDISCOUNT_DIRECTORY, 'media');
                $zipPath = $this->getFile($zipDestination['path'], 'Product_' . $timestamp . ".zip");
                $zipSource = self::CDISCOUNT_DIRECTORY . DS . 'Product_' . $timestamp;
                $zipSource = $this->createDir($zipSource);
                $this->createZip($zipSource['path'], $zipPath);
                $response = $this->sendProductPackage('Product_' . $timestamp . ".zip");
            }
            return $response;
        } catch (\Exception $e) {
            if ($this->debugMode == true) {
                $this->logger->error($e->getMessage(), ['path' => __METHOD__, 'trace' => $e->getTraceAsString()]);
            }
        }
        return $response;
    }

    /**
     * @param $data
     * @return bool
     */
    public function createOfferPackage($data)
    {
        try {
            $response = false;
            if (isset($data)) {
                $timestamp = $this->dateTime->gmtTimestamp();

                // Saving Content Type
                $contentTypes = $this->prepareContentType();
                $this->createContentType($contentTypes, $timestamp, 'Offer_');

                // Saving Product
                $path = self::CDISCOUNT_DIRECTORY . DS . 'Offer_' . $timestamp . DS . 'Content';
                $dir = $this->createDir($path);
                $name = 'Offers.xml';
                $path = $dir['path'] . DS . $name;
                $this->offerPath = $path;
                $product = $this->generator->create()->arrayToXml($data);
                $product->save($path);

                // Saving Relations
                $relations = $this->prepareRelation($timestamp, 'Offers');
                $this->createRelation($relations, $timestamp, 'Offer_');

                // Creating Zip File
                $zipDestination = $this->createDir(self::CDISCOUNT_DIRECTORY, 'media');
                $zipPath = $this->getFile($zipDestination['path'], 'Offer_' . $timestamp . ".zip");
                $zipSource = self::CDISCOUNT_DIRECTORY . DS . 'Offer_' . $timestamp;
                $zipSource = $this->createDir($zipSource);
                $this->createZip($zipSource['path'], $zipPath);
                $this->sendOfferPackage('Offer_' . $timestamp . ".zip");
                $response = true;
            }
        } catch (\Exception $e) {
            if ($this->debugMode == true) {
                $this->logger->error($e->getMessage(), ['path' => __METHOD__, 'trace' => $e->getTraceAsString()]);
            }
        }
        return $response;
    }

    /**
     * @param $name
     * @return bool
     */
    public function sendOfferPackage($name)
    {
        try {
            $response = true;
            $userName = $this->config->getUserName();
            $password = $this->config->getUserPassword();
            $client = $this->apiClient->create(['username' => $userName, 'password' => $password]);
            $url = $this->urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) .
                'cdiscount/' . $name;
            $token = $client->init();
            if (!empty($token)) {
                $offerPoint = $client->getOfferPoint();
                $submitOfferPackageResponse = $offerPoint->submitOfferPackage($url);
                if ($this->debugMode == true) {
                    $this->logger->info('Product Offer package sent.',
                        ['response' => (array)$submitOfferPackageResponse, 'method' => __METHOD__,
                            'package_name' => $name]);
                }
                $packageId = $submitOfferPackageResponse->getPackageId();
                if ($packageId) {
                    $getOfferPackageSubmissionResultResponse =
                        $offerPoint->getOfferPackageSubmissionResult((int)$packageId);
                    $this->saveResponse($getOfferPackageSubmissionResultResponse, 'OfferPackage', $name);
                } else {
                    $response = false;
                    if ($this->debugMode == true) {
                        $this->logger->error($submitOfferPackageResponse->getErrorMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            if ($this->config->getDebugMode() == true) {
                $this->config->logger->error($e->getMessage(), ['path' => __METHOD__, 'data' => $e->getTraceAsString()]);
            }
        }

        return $response;
    }

    /**
     * @param array $ids
     */
    public function syncFeeds($ids = [])
    {
        try {
            if (isset($ids) && !empty($ids)) {
                $userName = $this->config->getUserName();
                $password = $this->config->getUserPassword();
                $client = $this->apiClient->create(['username' => $userName, 'password' => $password]);
                $token = $client->init();
                $rPref = rand(0,5)."cd";
                $uniq = uniqid($rPref);
                if (!empty($token)) {
                    $productPoint = $client->getProductPoint();
                    $offerPoint = $client->getOfferPoint();
                    $feeds = $this->feeds->create()->getCollection()
                        ->addFieldToFilter('feed_id', ['in' => $ids]);
                    if ($feeds->getSize() > 0) {
                        foreach ($feeds as $feed) {
                            if ($feed->getType() == 'product_creation') {
                                $this->path = $feed->getFeedFile();
                                $getProductPackageSubmissionResult =
                                    $productPoint->getProductPackageSubmissionResult($feed->getFeedId());
                                $this->saveResponse($getProductPackageSubmissionResult, 'ProductPackage', $uniq);
                            } elseif ($feed->getType() == 'offer_creation') {
                                $this->offerPath = $feed->getFeedFile();
                                $getOfferPAckageSubmissionResult =
                                    $offerPoint->getOfferPackageSubmissionResult($feed->getFeedId());
                                $this->saveResponse($getOfferPAckageSubmissionResult, 'OfferPackage', $uniq);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            if ($this->debugMode == true) {
                $this->logger->error($exception->getMessage(), ['path' => __METHOD__, 'feeds' => $ids]);
            }
        }
    }

    public function sendProductPackage($name)
    {
        try {
            $response = true;
            $userName = $this->config->getUserName();
            $password = $this->config->getUserPassword();
            $client = $this->apiClient->create(['username' => $userName, 'password' => $password]);
            $url = $this->urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) .
                'cdiscount/' . $name;
            $token = $client->init();
            if (!empty($token)) {
                $productPoint = $client->getProductPoint();
                $submitProductPackageResponse = $productPoint->submitProductPackage($url);
                if ($this->debugMode == true) {
                    $this->logger->info('Product Package', ['response' =>
                        (array)$submitProductPackageResponse, 'method' => __METHOD__, 'package_name' => $name]);
                }
                $packageId = $submitProductPackageResponse->getPackageId();
                if ($packageId) {
                    $getProductPackageSubmissionResultResponse = $productPoint
                        ->getProductPackageSubmissionResult($packageId);
                    $this->saveResponse($getProductPackageSubmissionResultResponse, 'ProductPackage', $name);
                } else {
                    $this->saveFailedResponse($url, $submitProductPackageResponse->getErrorMessage(), $name);
                    if ($this->registry->registry('upload_error')) {
                        $this->registry->unregister('upload_error');
                    }

                    $this->registry->register('upload_error', $submitProductPackageResponse->getErrorMessage());
                    $response = false;
                    if ($this->debugMode == true) {
                        $this->logger->error($submitProductPackageResponse->getErrorMessage(), ['path' => __METHOD__]);
                    }
                }
            }
        } catch (\Exception $exception) {
            $response = false;
            if ($this->config->getDebugMode() == true) {
                $this->config->logger->error($exception->getMessage(),
                    ['path' => __METHOD__, 'data' => $exception->getTraceAsString()]);
            }
        }
        return $response;
    }

    public function getFile($path, $name = null)
    {

        if (!file_exists($path)) {
            @mkdir($path, 0775, true);
        }

        if ($name != null) {
            $path = $path . DS . $name;

            if (!file_exists($path)) {
                @file_put_contents($path, '');
            }
        }

        return $path;
    }

    public function createZip($source, $destination)
    {
        // Initialize archive object
        $zip = new \ZipArchive();
        $zip->open($destination, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        /** @var \SplFileInfo[] $files */
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($source) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();
    }

    public function deleteProducts($ids = [])
    {
        try {
            $userName = $this->config->getUserName();
            $password = $this->config->getUserPassword();
            $client = $this->apiClient->create(['username' => $userName, 'password' => $password]);
            $token = $client->init();

            if (!empty($ids)) {
                $sku = [];
                foreach ($ids as $id) {
                    $product = $this->product->create()
                        ->setStoreId($this->selectedStore)
                        ->load($id);
                    $this->ids[] = $product->getId();
                    // configurable Product
                    if ($product->getTypeId() == 'configurable' &&
                        $product->getVisibility() != 1
                    ) {
                        $configurableProduct = $product;
                        $productType = $configurableProduct->getTypeInstance();
                        $products = $productType->getUsedProducts($configurableProduct);
                        foreach ($products as $product) {
                            $sku[] = $product->getSku();
                        }
                    } elseif ($product->getTypeId() == 'simple' &&
                        $product->getVisibility() != 1
                    ) {
                        $sku[] = $product->getSku();
                    }
                    if (!empty($token)) {
                        if (isset($sku) and !empty($sku)) {
                            $offerPoint = $client->getOfferPoint();
                            $response = $submitPackage = $offerPoint->submitOfferStateAction($sku, 'Unpublish');
                        }
                    }
                }
                if (!$response->hasError()) {
                    $this->updateStatus($this->ids, \Ced\Cdiscount\Model\Source\Product\Status::NOT_UPLOADED);
                } elseif ($response->hasError()) {
                    return $response->getErrorMessage();
                }
                $response = true;
            }
        } catch (\Exception $e) {
            if ($this->debugMode == true) {
                $this->logger->error($e->getMessage(), ['path' => __METHOD__]);
            }
        }
        return $response;
    }

    public function publishProducts($ids = [])
    {
        try {
            $userName = $this->config->getUserName();
            $password = $this->config->getUserPassword();
            $client = $this->apiClient->create(['username' => $userName, 'password' => $password]);
            $token = $client->init();

            if (!empty($ids)) {
                $sku = [];
                foreach ($ids as $id) {
                    $product = $this->product->create()
                        ->setStoreId($this->selectedStore)
                        ->load($id);
                    $this->ids[] = $product->getId();
                    // configurable Product
                    if ($product->getTypeId() == 'configurable' &&
                        $product->getVisibility() != 1
                    ) {
                        $configurableProduct = $product;
                        $productType = $configurableProduct->getTypeInstance();
                        $products = $productType->getUsedProducts($configurableProduct);
                        foreach ($products as $product) {
                            $sku[] = $product->getSku();
                        }
                    } elseif ($product->getTypeId() == 'simple' &&
                        $product->getVisibility() != 1
                    ) {
                        $sku[] = $product->getSku();
                    }
                    if (!empty($token)) {
                        if (isset($sku) && !empty($sku)) {
                            $offerPoint = $client->getOfferPoint();
                            $response = $submitPackage = $offerPoint->submitOfferStateAction($sku, 'Publish');
                        }
                    }
                }
                if (!$response->hasError()) {
                    $this->updateStatus($this->ids, \Ced\Cdiscount\Model\Source\Product\Status::LIVE);
                } elseif ($response->hasError()) {
                    return $response->getErrorMessage();
                }
                $response = true;
            }
        } catch (\Exception $e) {
            if ($this->debugMode == true) {
                $this->logger->error($e->getMessage(), ['path' => __METHOD__]);
            }
        }
        return $response;
    }

    /**
     * @param array $ids
     * @return array
     * @throws \Exception
     */
    public function validateAllProducts($ids = [])
    {
        $validatedProducts = [
            'simple' => [],
            'configurable' => [],
        ];
        $this->ids = [];
        foreach ($ids as $id) {

            $product = $this->product->create()
                ->setStoreId($this->selectedStore)
                ->load($id);
            // Getting product profile

            // 1.1: Getting Parents and loading parent profile and sending product as child.
            $productParents = $this->objectManager
                ->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
                ->getParentIdsByChild($product->getId());
            if (!empty($productParents)) {
                /** @var \Ced\Cdiscount\Helper\Profile $profile */
                $profile = $this->profileHelper->getProfile($productParents[0]);
                if (!empty($profile->getId())) {
                    $product = $this->product->create()
                        ->setStoreId($this->selectedStore)
                        ->load($productParents[0]);
                } else {
                    // 1.1.2: Getting product profile id and sending as simple product.
                    /** @var \Ced\Cdiscount\Helper\Profile $profile */
                    $profile = $this->profileHelper->getProfile(
                        $id,
                        $product->getData(\Ced\Cdiscount\Helper\Profile::ATTRIBUTE_CODE_PROFILE_ID)
                    );
                    if (empty($profile->getId())) {
                        $e = [];
                        $e[$product->getSku()] = [
                            'sku' => $product->getSku(),
                            'id' => $product->getId(),
                            'url' => $this->urlBuilder
                                ->getUrl('catalog/product/edit', ['id' => $product->getId()]),
                            'errors' => [['profile_missing' => 'Please assign product to a profile and try again.']]
                        ];
                        $product->setData(\Ced\Cdiscount\Helper\Profile::ATTRIBUTE_CODE_VALIDATION_ERRORS, $this->json->jsonEncode($e));
                        $product->getResource()
                            ->saveAttribute($product, \Ced\Cdiscount\Helper\Profile::ATTRIBUTE_CODE_VALIDATION_ERRORS);
                        continue;
                    }
                }
            } else {
                // 1.2: Getting product profile id and sending as simple product.
                /** @var \Ced\Cdiscount\Helper\Profile $profile */
                $profile = $this->profileHelper->getProfile(
                    $id,
                    $product->getData(\Ced\Cdiscount\Helper\Profile::ATTRIBUTE_CODE_PROFILE_ID)
                );
                if (empty($profile->getId())) {
                    $e = [];
                    $e[$product->getSku()] = [
                        'sku' => $product->getSku(),
                        'id' => $product->getId(),
                        'url' => $this->urlBuilder
                            ->getUrl('catalog/product/edit', ['id' => $product->getId()]),
                        'errors' => ['profile_missing' => 'Please assign product to a profile and try again.']
                    ];
                    $product->setData(\Ced\Cdiscount\Helper\Profile::ATTRIBUTE_CODE_VALIDATION_ERRORS,
                        $this->json->jsonEncode($e));
                    $product->getResource()
                        ->saveAttribute($product,
                            \Ced\Cdiscount\Helper\Profile::ATTRIBUTE_CODE_VALIDATION_ERRORS);
                    continue;
                }
            }
            // case 1 : for config products
            if ($product->getTypeId() == 'configurable' &&
                $product->getVisibility() != 1
            ) {
                $configurableProduct = $product;
                $sku = $configurableProduct->getSku();
                $parentId = $configurableProduct->getId();
                $productType = $configurableProduct->getTypeInstance();
                $products = $productType->getUsedProducts($configurableProduct);
                $attributes = $productType->getConfigurableAttributesAsArray($configurableProduct);
                $magentoVariantAttributes = [];
                foreach ($attributes as $attribute) {
                    $magentoVariantAttributes[] = $attribute['attribute_code'];
                }
                $cdiscountVariantAttributes = $profile->getAttributes();
                $errors = [
                    $sku => [
                        'sku' => $sku,
                        'id' => $configurableProduct->getId(),
                        'url' => $this->urlBuilder
                            ->getUrl('catalog/product/edit', ['id' => $configurableProduct->getId()]),
                        'errors' => []
                    ]
                ];
                //common attributes check start
                $commonErrors = [];
                // Taking The Attributes From Configuration Which Are To Be Send From Configurable Products
                $requiredAttribute = $this->config->getConfAttrValues();
                foreach ($profile->getRequiredAttributes() as $attributeId => $validationAttribute) {
                    if (in_array($attributeId, $requiredAttribute)) {
                        $value = $configurableProduct->getData($validationAttribute[self::MAGENTO_ATTRIBUTE_CODE]);
                        if (!isset($value) || empty($value)) {
                            $commonErrors[$attributeId] = 'Common required attribute empty.';
                        }
                    }
                }
                if (!empty($commonErrors)) {
                    $errors[$sku]['errors'][] = $commonErrors;
                }
                //common attributes check end.

                // variant attribute mapping check start.
                $unmappedVariantAttribute = [];
                $mappedVariantAttributes = [];

                foreach ($magentoVariantAttributes as $code) {
                    foreach ($cdiscountVariantAttributes as $cdiscountVariantAttribute) {
                        if (isset($cdiscountVariantAttribute[self::MAGENTO_ATTRIBUTE_CODE]) and
                            $cdiscountVariantAttribute[self::MAGENTO_ATTRIBUTE_CODE] == $code
                        ) {
                            $mappedVariantAttributes[] = $code;
                            break;
                        }
                    }
                }
                foreach ($magentoVariantAttributes as $code) {
                    if (!in_array($code, $mappedVariantAttributes)) {
                        $unmappedVariantAttribute[] = $code;
                    }
                }
                $cdiscountVariantAttributesValues = [];
                foreach ($cdiscountVariantAttributes as $attributeId => $variantAttribute) {
                    if (isset($variantAttribute[self::MAGENTO_ATTRIBUTE_CODE]) and
                        in_array($variantAttribute[self::MAGENTO_ATTRIBUTE_CODE], $mappedVariantAttributes)) {
                        $cdiscountVariantAttributesValues[$variantAttribute[self::MAGENTO_ATTRIBUTE_CODE]] =
                            $variantAttribute;
                    }
                }
                // variant attribute mapping check end.

                $key = 0;
                foreach ($products as $product) {
                    $errors[$product->getSku()] = [
                        'sku' => $product->getSku(),
                        'id' => $product->getId(),
                        'url' => $this->urlBuilder
                            ->getUrl('catalog/product/edit', ['id' => $product->getId()]),
                        'errors' => []
                    ];
                    $product = $this->product->create()
                        ->setStoreId($this->selectedStore)
                        ->load($product->getId());
                    $productId = $this->validateProduct($product->getId(), $product, $profile, $parentId);
                    // variant attribute option value check start.
                    foreach ($mappedVariantAttributes as $mappedVariantAttribute) {
                        if (isset($cdiscountVariantAttributesValues[$mappedVariantAttribute]['options'])
                            && !empty($cdiscountVariantAttributesValues[$mappedVariantAttribute]['options'])) {
                            $valueId = $product->getData($mappedVariantAttribute);
                            $value = $valueId;
                            $defaultValue = "";

                            if (isset($cdiscountVariantAttributesValues[$mappedVariantAttribute]['default_value']) and
                                !empty($cdiscountVariantAttributesValues[$mappedVariantAttribute]['default_value'])
                            ) {
                                $defaultValue =
                                    $cdiscountVariantAttributesValues[$mappedVariantAttribute]['default_value'];
                            }

                            //case 3: magento attribute option value
                            $attr = $product->getResource()->getAttribute($mappedVariantAttribute)
                                ->setStoreId($this->selectedStore);
                            if ($attr && ($attr->usesSource() || $attr->getData('frontend_input') == 'select')) {
                                $value = $attr->getSource()->getOptionText($valueId);
                                if (is_object($value)) {
                                    $value = $value->getText();
                                }
                            }
                            // order of check: default value > option mapping > default magento option value
                            if (!isset($cdiscountVariantAttributesValues[$mappedVariantAttribute]
                                    ['options'][$defaultValue]) &&
                                !isset($cdiscountVariantAttributesValues[$mappedVariantAttribute]
                                    ['option_mapping'][$valueId]) &&
                                !isset($cdiscountVariantAttributesValues[$mappedVariantAttribute]
                                    ['options'][str_replace("'", "&#39;", $value)])
                            ) {
//                                if (isset($value) && !empty($value)) {
                                //if (!in_array($value, $cdiscountVariantAttributesValues[$mappedVariantAttribute]['options'])) {
                                $errors[$product->getSku()]['errors'][][$mappedVariantAttribute] =
                                    "attribute has invalid option value: <b> " .
                                    $value . " [" . json_encode($valueId) . "]";
                                //}
//
//                                }
                            }
                        }
                    }
                    // variant attribute option value check end.
                    if (isset($productId['id']) &&
                        empty($errors[$sku]['errors']) &&
                        empty($errors[$product->getSku()]['errors'])
                    ) {
                        //Check if all mappedAttributes are mapped
                        if (empty($unmappedVariantAttribute)) {
                            $validatedProducts['configurable'][$parentId][$product->getId()]['id'] = $productId['id'];
                            $validatedProducts['configurable'][$parentId][$product->getId()]['type'] = 'configurable';
                            $validatedProducts['configurable'][$parentId][$product->getId()]['variantid'] = $sku;
                            $validatedProducts['configurable'][$parentId][$product->getId()]['parentid'] = $parentId;
                            $validatedProducts['configurable'][$parentId][$product->getId()]['variantattr'] =
                                $mappedVariantAttributes;
                            $validatedProducts['configurable'][$parentId][$product->getId()]['variantattrmapped'] =
                                $cdiscountVariantAttributesValues;
                            $validatedProducts['configurable'][$parentId][$product->getId()]['isprimary'] = 'false';
                            $validatedProducts['configurable'][$parentId][$product->getId()]['isprimary'] = 'false';
                            $validatedProducts['configurable'][$parentId][$product->getId()]['category'] =
                                $profile->getProfileCategory();
                            $validatedProducts['configurable'][$parentId][$product->getId()]['profile_id'] =
                                $profile->getId();
                            if ($key == 0) {
                                $validatedProducts['configurable'][$parentId][$product->getId()]['isprimary'] = 'true';
                                $key = 1;
                            }
                            $product->setData('cdiscount_validation_errors',
                                $this->json->jsonEncode('valid'));
                            $product->getResource()
                                ->saveAttribute($product, 'cdiscount_validation_errors');
                            continue;
                        } else {
                            $errorIndex = implode(", ", $unmappedVariantAttribute);
                            $errors[$product->getSku()]['errors'][][$errorIndex] = [
                                'Configurable attributes not mapped.'];
                        }
                    } elseif (isset($productId['errors'])) {
                        $errors[$product->getSku()]['errors'][] = $productId['errors'];
                        if (!empty($unmappedVariantAttribute)) {
                            $errorIndex = implode(", ", $unmappedVariantAttribute);
                            $errors[$product->getSku()]['errors'][][$errorIndex] = [
                                'Configurable attributes not mapped.'];
                        }
                    }
                }
                if (!empty($errors)) {
                    if (!empty($unmappedVariantAttribute)) {
                        $errorIndex = implode(", ", $unmappedVariantAttribute);
                        $errors[$configurableProduct->getSku()]['errors'][][$errorIndex] = [
                            'Configurable attributes not mapped.'];
                    }
                    $errorsInRegistry = $this->registry->registry('cdiscount_product_validaton_errors');
                    $this->registry->unregister('cdiscount_product_validaton_errors');
                    $this->registry->register(
                        'cdiscount_product_validaton_errors',
                        is_array($errorsInRegistry) ? array_merge($errorsInRegistry, $errors) : $errors
                    );
                    $configurableProduct->setCdiscountValidationErrors($this->json->jsonEncode($errors));
                    $configurableProduct->getResource()
                        ->saveAttribute($configurableProduct, 'cdiscount_validation_errors');
                } else {
                    $configurableProduct->setCdiscountValidationErrors('["valid"]');
                    $configurableProduct->getResource()
                        ->saveAttribute($configurableProduct, 'cdiscount_validation_errors');
                }
            } elseif ($product->getTypeId() == 'simple' && $product->getVisibility() != 1) {
                // case 2 : for simple products
                $productId = $this->validateProduct($product->getId(), $product, $profile);
                if (isset($productId['id'])) {
                    $validatedProducts['simple'][$product->getId()] = [
                        'id' => $productId['id'],
                        'type' => 'simple',
                        'variantid' => null,
                        'variantattr' => null,
                        'category' => $profile->getProfileCategory(),
                        'profile_id' => $profile->getId()
                    ];
                } elseif (isset($productId['errors']) && is_array($productId['errors'])) {
                    $errors[$product->getSku()] = [
                        'sku' => $product->getSku(),
                        'id' => $product->getId(),
                        'url' => $this->urlBuilder
                            ->getUrl('catalog/product/edit', ['id' => $product->getId()]),
                        'errors' => $productId['errors']
                    ];
                    $errorsInRegistry = $this->registry->registry('cdiscount_product_validaton_errors');
                    $this->registry->unregister('cdiscount_product_validaton_errors');
                    $this->registry->register(
                        'cdiscount_product_validaton_errors',
                        is_array($errorsInRegistry) ? array_merge($errorsInRegistry, $errors) :
                            $errors
                    );
                }
            }
        }
        return $validatedProducts;
    }

    /**
     * @param $id
     * @param null $product
     * @param null $profile
     * @param null $parentId
     * @return bool
     * @throws \Exception
     */
    public function validateProduct($id, $product = null, $profile = null, $parentId = null)
    {
        $validatedProduct = false;

        //if product object is not passed, then load in case of Simple product
        if ($product == null) {
            $product = $this->product->create()
                ->setStoreId($this->selectedStore)
                ->load($id);
        }
        //if profile is not passed, get profile
        if ($profile == null) {
            $profile = $this->profileHelper->getProfile($product->getId(), $product->getCdiscountProfileId());
        }
        $profileId = $profile->getId();
        $sku = $product->getSku();
        $productArray = $product->toArray();
        $errors = [];


        //Case 1: Profile is Available
        if (isset($profileId) && $profileId != false) {
            $category = $profile->getProfileCategory();
            $requiredAttributes = $profile->getRequiredAttributes();
            foreach ($requiredAttributes as $cdiscountAttributeId => $cdiscountAttribute) {


                $requiredAttribute = $this->config->getConfAttrValues();
                if (isset($parentId) && !empty($parentId) &&
                    in_array($cdiscountAttributeId, $requiredAttribute)) {
                    // Validation case 1 skip some attributes that are not to be validated.
                    continue;
                } elseif (!isset($productArray[$cdiscountAttribute[self::MAGENTO_ATTRIBUTE_CODE]])
                    || empty($productArray[$cdiscountAttribute[self::MAGENTO_ATTRIBUTE_CODE]]) &&
                    empty($cdiscountAttribute['default_value'])
                ) {
                    // Validation case 2 Empty or blank value check
                    $errors["$cdiscountAttributeId"] = "Required attribute empty or not mapped. 
                    [{$cdiscountAttribute[self::MAGENTO_ATTRIBUTE_CODE]}]";
                } elseif (isset($cdiscountAttribute['options']) &&
                    !empty($cdiscountAttribute['options'])
                ) {
                    $valueId = $product->getData($cdiscountAttribute[self::MAGENTO_ATTRIBUTE_CODE]);
                    $value = $valueId;
                    $defaultValue = "";
                    // Case 2: default value from profile
                    if (isset($cdiscountAttribute['default_value']) &&
                        !empty($cdiscountAttribute['default_value'])
                    ) {
                        $defaultValue = $cdiscountAttribute['default_value'];
                    }
                    // Case 3: magento attribute option value
                    $attr = $product->getResource()->getAttribute($cdiscountAttribute[self::MAGENTO_ATTRIBUTE_CODE])
                        ->setStoreId($this->selectedStore);
                    if ($attr && ($attr->usesSource() || $attr->getData('frontend_input') == 'select')) {
                        $value = $attr->getSource()->getOptionText($valueId);
                        if (is_object($value)) {
                            $value = $value->getText();
                        }
                    }
                    // order of check: default value > option mapping > default magento option value
                    if (!isset($cdiscountAttribute['options'][$defaultValue]) &&
                        !isset($cdiscountAttribute['option_mapping'][$valueId]) &&
                        !isset($cdiscountAttribute['options'][$value])
                    ) {
//                        if (isset($value) && !empty($value)) {
                        //if (!in_array($value, $cdiscountAttribute['options'])) {
                        $errors["$cdiscountAttributeId"] = "Cdiscount attribute: 
                                [" . $cdiscountAttribute['name'] .
                            "] mapped with [" . $cdiscountAttribute[self::MAGENTO_ATTRIBUTE_CODE] .
                            "] has invalid option value: <b> " .
                            json_encode($value) . "/" . json_encode($valueId) .
                            "</b> ";
                        //}
//                        }
                    }
                }
                if ($cdiscountAttributeId == \Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN &&
                    !isset($errors[$cdiscountAttributeId])) {
                    $value = $productArray[$cdiscountAttribute[self::MAGENTO_ATTRIBUTE_CODE]];
                    $ean = $this->barcode->setBarcode($value);
                    if (!$ean->isValid()) {
                        $errors["$cdiscountAttributeId"] = "Cdiscount attribute: [" . $cdiscountAttribute['name'] .
                            "] mapped with [" . $cdiscountAttribute[self::MAGENTO_ATTRIBUTE_CODE] .
                            "] has invalid Ean '{$value}'.";
                    }

                }

            }
            $img = $this->prepareImages($product);
            if (empty($img) && !empty($parentId)) {
                $parentImages = $this->product->create()->load($parentId)->getMediaGalleryImages();
                if ($parentImages->getSize() > 0) {
                    foreach ($parentImages as $parentImage) {
                        $img[] = $parentImage->getUrl();

                    }
                }
            }
            if (empty($img)) {
                $errors["ProductImage"] = "ProductImage Not Available";
            }

            //Setting Errors in product validation attribute
            if (!empty($errors)) {
                $validatedProduct['errors'] = $errors;
                $e = [];
                $e[$product->getSku()] = [
                    'sku' => $product->getSku(),
                    'id' => $product->getId(),
                    'url' => $this->urlBuilder
                        ->getUrl('catalog/product/edit', ['id' => $product->getId()]),
                    'errors' => [$errors]
                ];
                $product->setCdiscountValidationErrors($this->json->jsonEncode($e));
                $product->getResource()
                    ->saveAttribute($product, 'cdiscount_validation_errors');
            } else {
                // insert product id for status update.
                $this->ids[] = $product->getId();

                $product->setData('cdiscount_validation_errors', '["valid"]');
                $product->getResource()
                    ->saveAttribute($product, 'cdiscount_validation_errors');
                $validatedProduct['id'] = $id;
                $validatedProduct['category'] = $category;
            }
        } else {
            //Case 2: Profile is not available, not needed case
            $errors = [
                "sku" => "$sku",
                "id" => "$id",
                "url" => $this->urlBuilder
                    ->getUrl('catalog/product/edit', ['id' => $product->getId()]),
                "errors" =>
                    [
                        "Profile not found" => "Product is not mapped in any cdiscount profile"
                    ]
            ];
            $validatedProduct['errors'] = $errors;
            $errors = $this->json->jsonEncode([$errors]);
            $product->setData('cdiscount_validation_errors', $errors);
            $product->getResource()
                ->saveAttribute($product, 'cdiscount_validation_errors');
        }
        return $validatedProduct;
    }


    /**
     * @param array $ids
     * @throws \Exception
     */
    private function prepareSimpleProducts($ids = [])
    {
        try {
            foreach ($ids as $id) {
                $product = $this->product->create()
                    ->setStoreId($this->selectedStore)
                    ->load($id['id']);
                $categoryIds = ($product->getCategoryIds());
                $productCategoryName = [];
                foreach ($categoryIds as $categoryId) {
                    $category = $this->objectManager->create('Magento\Catalog\Model\Category')
                        ->load($categoryId);
                    $productCategoryName[] = ($category->getName());
                }
                $productCategoryName = implode('', $productCategoryName);
                $modelAttr = $productImages = [];
                $images = $this->prepareImages($product);
                if (is_array($images)) {
                    foreach ($images as $image) {
                        $productImages[] = [
                            'ProductImage' => [
                                '_attribute' => [
                                    'Uri' => $image
                                ],
                                '_value' => []
                            ]
                        ];
                    }
                }
                $profile = $this->profileHelper->getProfile($product->getId(), $id['profile_id']);
                $categoryPath = $this->categoriesFactory->create()
                    ->addFieldToFilter('code', ['eq' => $profile->getProfileCategory()])
                    ->addFieldToSelect('path')->getFirstItem()->getData('path');
                $this->ids[] = $product->getId();
                $requiredAttributes = $profile->getRequiredAttributes();
                $optionalAttributes = $profile->getOptionalAttributes();
                $modelValues = $this
                    ->prepareAttributes($product, $profile, \Ced\Cdiscount\Helper\Profile::ATTRIBUTE_TYPE_MODAL);
                if (is_array($modelValues)) {
                    foreach ($modelValues as $modelKey => $modelValue) {
                        $modelAttr[] = ['x:String' => [
                            '_attribute' => [
                                'x:Key' => $modelKey
                            ],
                            '_value' => $modelValue
                        ],
                        ];
                    }
                }
                $brandName = $product->getResource()
                    ->getAttribute($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_BRAND_NAME]
                    [self::MAGENTO_ATTRIBUTE_CODE])
                    ->setStoreId($this->selectedStore);
                if ($brandName &&
                    ($brandName->usesSource() || $brandName->getData('frontend_input') == 'select')) {

                    $brandValue = $brandName
                        ->getSource()
                        ->getOptionText($product
                            ->getData($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_BRAND_NAME]
                            [self::MAGENTO_ATTRIBUTE_CODE]));
                    if (is_object($brandValue) && method_exists($brandValue, 'getText')) {
                        $brandValue = $brandValue->getText();
                    }

                } else {
                    $brandValue = $product
                        ->getData($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_BRAND_NAME]
                        [self::MAGENTO_ATTRIBUTE_CODE]);
                }

                $description = $product
                    ->getData($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_DESCRIPTION]
                    [self::MAGENTO_ATTRIBUTE_CODE]);

                $longLabel = $product
                    ->getData($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_LONG_LABEL]
                    [self::MAGENTO_ATTRIBUTE_CODE]);

                $shortLabel = $product
                    ->getData($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_SHORT_LABEL]
                    [self::MAGENTO_ATTRIBUTE_CODE]);

                $htmlToText = $this->htmlToText->create();

                $this->data['ProductPackage']['_value']['ProductPackage.Products']
                ['_value']['ProductCollection']['_value'][$this->key] = [
                    'Product' => [
                        '_attribute' => [
                            'CategoryCode' => $profile->getProfileCategory(),
                            'SellerProductId' => $product
                                ->getData($requiredAttributes
                                [\Ced\Cdiscount\Helper\Category::ATTRIBUTE_SELLER_PRODUCT_ID]
                                [self::MAGENTO_ATTRIBUTE_CODE]),
                            'BrandName' => $htmlToText::convert($brandValue),
                            'ShortLabel' => $htmlToText::convert($shortLabel),
                            'LongLabel' => $htmlToText::convert($longLabel),
                            'Description' => $htmlToText::convert($description),
                            'ProductKind' => 'Standard',
                            'Model' => $profile->getModelName(),
                            'Navigation' => isset($categoryPath) ? $categoryPath : $productCategoryName
                            /*'EncodedMarketingDescription' => base64_encode($product
                                ->getData($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_LONG_LABEL]
                                [self::MAGENTO_ATTRIBUTE_CODE]))*/
                        ],
                        '_value' => [
                            /*'Product.EanList' => [
                                '_attribute' => [],
                                '_value' => [
                                    'ProductEan' => [
                                        '_attribute' => [
                                            'Ean' => $product
                                                ->getData($requiredAttributes
                                                [\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                                                [self::MAGENTO_ATTRIBUTE_CODE])
                                        ],
                                        '_value' => []
                                    ],
                                ],
                            ],*/
                            'Product.ModelProperties' => [
                                '_attribute' => [],
                                '_value' => $modelAttr
                            ]
                        ]
                    ]
                ];

                if (isset($requiredAttributes
                    [\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                    [self::MAGENTO_ATTRIBUTE_CODE]) && !empty($requiredAttributes
                    [\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                    [self::MAGENTO_ATTRIBUTE_CODE])) {

                    $this->data['ProductPackage']['_value']['ProductPackage.Products']
                    ['_value']['ProductCollection']
                    ['_value'][$this->key]['Product']['_value']['Product.EanList'] = [
                        '_attribute' => [],
                        '_value' => [
                            'ProductEan' => [
                                '_attribute' => [
                                    'Ean' => $product
                                        ->getData($requiredAttributes
                                        [\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                                        [self::MAGENTO_ATTRIBUTE_CODE])
                                ],
                                '_value' => []
                            ],
                        ],
                    ];
                }

                if (isset($productImages) and !empty($productImages)) {
                    $this->data['ProductPackage']['_value']['ProductPackage.Products']
                    ['_value']['ProductCollection']
                    ['_value'][$this->key]['Product']
                    ['_value']['Product.Pictures'] = [
                        '_attribute' => [],
                        '_value' => $productImages
                    ];
                }

                if (isset($optionalAttributes) and !empty($optionalAttributes)) {
                    $optionalAttributes = $this
                        ->prepareAttributes($product, $profile,
                            \Ced\Cdiscount\Helper\Profile::ATTRIBUTE_TYPE_OPTIONAL);

                    foreach ($optionalAttributes as $attributeName => $optionalValue) {
                        $this->data['ProductPackage']['_value']
                        ['ProductPackage.Products']['_value']
                        ['ProductCollection']['_value'][$this->key]
                        ['Product']['_attribute']["{$attributeName}"] = $optionalValue;
                    }
                }

                $this->key++;
            }
        } catch (\Exception $exception) {
            if ($this->config->getDebugMode() == true) {
                $this->logger->error($exception->getMessage(),
                    ['path' => __METHOD__, 'trace' => $exception->getTraceAsString()]);
            }
        }

    }

    private function prepareRelation($timestamp, $type = 'Products')
    {
        $relation = [
            'Relationships' =>
                [
                    '_attribute' => [
                        'xmlns' => 'http://schemas.openxmlformats.org/package/2006/relationships',
                    ],

                    '_value' => [
                        'Relationship' => [
                            '_attribute' => [
                                'Type' => "http://www.cdiscount.com/uri/document",
                                'Target' => "/Content/$type.xml",
                                'Id' => $timestamp,
                            ],
                            '_value' => [
                            ]
                        ]
                    ]
                ]
        ];
        return $relation;
    }

    private function prepareContentType()
    {
        $contentType = [
            'Types' =>
                [
                    '_attribute' => [
                        'xmlns' => 'http://schemas.openxmlformats.org/package/2006/content-types',
                    ],

                    '_value' => [
                        '0' => [
                            'Default' => [
                                '_attribute' => [
                                    'Extension' => "xml",
                                    'ContentType' => 'text/xml'
                                ],
                                '_value' => [
                                ]
                            ]
                        ],
                        '1' => [
                            'Default' => [
                                '_attribute' => [
                                    'Extension' => "rels",
                                    'ContentType' => 'application/vnd.openxmlformats-package.relationships+xml'
                                ],
                                '_value' => [
                                ]
                            ]
                        ]
                    ]
                ]
        ];
        return $contentType;
    }


    public function prepareShipmentData()
    {
        $values = $this->getShippingDetails();
        $offerShippingData = [
            '_attribute' => [],
            '_value' => [
                'ShippingInformationList' => [
                    '_attribute' => [
                        'Capacity' => count($values),
                    ],
                    '_value' => $values
                ]
            ]
        ];
        return $offerShippingData;
    }

    public function getShippingDetails()
    {
        $shippingInformationList = [];
        $i = 0;
        $shippingMethods = $this->config->getShippingMethods();
        if (isset($shippingMethods) and !empty(($shippingMethods))) {
            if (count($shippingMethods) > 0) {
                foreach ($shippingMethods as $shippingMethod) {
                    $shippingInformationList[$i++] = [
                        'ShippingInformation' => [
                            '_attribute' => [
                                'AdditionalShippingCharges' =>
                                    number_format(isset($shippingMethod['additional_price'])
                                        ? (float)$shippingMethod['additional_price'] : 2, 2),
                                'DeliveryMode' => isset($shippingMethod['shipping_method'])
                                    ? $shippingMethod['shipping_method'] : 'Standard',
                                'ShippingCharges' => number_format(isset($shippingMethod['price'])
                                    ? (float)$shippingMethod['price'] : 1, 2),
                            ],
                            '_value' => []
                        ]
                    ];
                }
            } else {
                if ($this->debugMode == true) {
                    $this->logger->debug(
                        'No shipping methods available.',
                        ['data' => $shippingMethods, 'class' => __CLASS__, 'method' => __METHOD__]
                    );
                }
            }

        } else {
            if ($this->debugMode == true) {
                $this->logger->debug(
                    'No shipping methods available.',
                    ['data' => $shippingMethods, 'class' => __CLASS__, 'method' => __METHOD__]
                );
            }

        }
        return $shippingInformationList;
    }

    /**
     * @param $productObject
     * @return array
     */
    public function getPrice($productObject)
    {
        $splprice =
            round((float)$productObject->setStoreId($this->selectedStore)->getFinalPrice(), 2);

        $price =
            round((float)$productObject->setStoreId($this->selectedStore)->getData('price'), 2);

        $splprice = (float)number_format($splprice, 2);
        $price = (float)number_format($price, 2);

        if ($splprice == $price) {
            $splprice =
                round((float)$productObject->setStoreId($this->selectedStore)->getSpecialPrice(), 2);
            $splprice = (float)number_format($splprice, 2);
        }

        $configPrice = $this->config->getPriceType();

        switch ($configPrice) {
            case 'plus_fixed':
                $fixedPrice = $this->config->getFixedPrice();
                $price = $this->forFixPrice($price, $fixedPrice, 'plus_fixed');
                $splprice = $this->forFixPrice($splprice, $fixedPrice, 'plus_fixed');
                break;

            case 'min_fixed':
                $fixedPrice = $this->config->getFixedPrice();
                $price = $this->forFixPrice($price, $fixedPrice, 'min_fixed');
                $splprice = $this->forFixPrice($splprice, $fixedPrice, 'min_fixed');
                break;

            case 'plus_per':
                $percentPrice = $this->config->getPercentPrice();
                $price = $this->forPerPrice($price, $percentPrice, 'plus_per');
                $splprice = $this->forPerPrice($splprice, $percentPrice, 'plus_per');
                break;

            case 'min_per':
                $percentPrice = $this->config->getPercentPrice();
                $price = $this->forPerPrice($price, $percentPrice, 'min_per');
                $splprice = $this->forPerPrice($splprice, $percentPrice, 'min_per');
                break;

            case 'differ':
                $customPriceAttr = $this->config->getDifferPrice();
                try {
                    $cprice = (float)$productObject->getData($customPriceAttr);
                } catch (\Exception $e) {
                    $this->logger->debug(" Cdiscount: Product Helper: getCdiscountPrice() : " .
                        $e->getMessage());
                }
                $price = (isset($cprice) && $cprice != 0) ? $cprice : $price;
                $splprice = $price;
                break;

            default:
                return [
                    'price' => (string)$price,
                    'special_price' => (string)$splprice,
                ];
        }

        return [
            'price' => (string)$price,
            'special_price' => (string)$splprice,
        ];
    }

    /**
     * ForFixPrice
     * @param null $price
     * @param null $fixedPrice
     * @param string $configPrice
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
     * ForPerPrice
     * @param null $price
     * @param null $percentPrice
     * @param string $configPrice
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

    /**
     * @param $product
     * @return array
     */
    private function prepareImages($product)
    {
        $productImages = $product->getMediaGalleryImages();
        $mainImage = $product->getData('image');
        $images = [];
        if ($productImages->getSize() > 0) {
            foreach ($productImages as $image) {

                $images[] = $image->getUrl();
            }
        }
        return $images;
    }

    /**
     * @param array $ids
     * @throws \Exception
     */
    private function prepareConfigurableProducts($ids = [])
    {
        try {
            foreach ($ids as $parentId => $products) {
                $configurableProduct = $this->product->create()
                    ->setStoreId($this->selectedStore)
                    ->load($parentId);
                $this->ids[] = $configurableProduct->getId();

                // Adding Variant Items
                foreach ($products as $productId => $id) {
                    $productIds[] = $id;
                    $product = $this->product->create()
                        ->setStoreId($this->selectedStore)
                        ->load($productId);
                    $this->ids[] = $product->getId();
                    $categoryIds = ($product->getCategoryIds());
                    $productCategoryName = [];
                    foreach ($categoryIds as $categoryId) {
                        $category = $this->objectManager->create('Magento\Catalog\Model\Category')
                            ->load($categoryId);
                        $productCategoryName[] = ($category->getName());
                    }
                    $productCategoryName = implode('', $productCategoryName);
                    $modelAttr = $productImages = [];
                    $images = $this->prepareImages($product);
                    if (empty($images)) {
                        $configImages = $configurableProduct->getMediaGalleryImages();
                        if ($configImages->getSize() > 0) {
                            foreach ($configImages as $configImage) {
                                $images[] = $configImage->getUrl();
                            }
                        }
                    }

                    if (is_array($images)) {
                        foreach ($images as $image) {
                            $productImages[] = [
                                'ProductImage' => [
                                    '_attribute' => [
                                        'Uri' => $image
                                    ],
                                    '_value' => []
                                ]
                            ];
                        }
                    }

                    $profile = $this->profileHelper->getProfile($product->getId(), $id['profile_id']);
                    $categoryPath = $this->categoriesFactory->create()
                        ->addFieldToFilter('code', ['eq' => $profile->getProfileCategory()])
                        ->addFieldToSelect('path')->getFirstItem()->getData('path');
                    $this->ids[] = $product->getId();

                    $requiredAttributes = $profile->getRequiredAttributes();
                    $optionalAttributes = $profile->getOptionalAttributes();
                    $modelValues = $this
                        ->prepareAttributes($product, $profile,
                            \Ced\Cdiscount\Helper\Profile::ATTRIBUTE_TYPE_MODAL);

                    if (is_array($modelValues)) {
                        foreach ($modelValues as $modelKey => $modelValue) {
                            $modelAttr[] = ['x:String' => [
                                '_attribute' => [
                                    'x:Key' => $modelKey
                                ],
                                '_value' => $modelValue
                            ],
                            ];
                        }
                    }

                    if ($this->debugMode == true) {
                        $this->logger->info('Model Attr', ['path' => __METHOD__, 'data' => $modelValues]);
                    }

                    // Attributes that are save in configuration to be taken from Config Product
                    $requiredAttributeConfType = $this->config->getConfAttrValues();
                    // Attribute Brand Name Prepared from Simple Product Initially
                    $brandName = $product
                        ->getResource()
                        ->getAttribute($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_BRAND_NAME]
                        [self::MAGENTO_ATTRIBUTE_CODE])->setStoreId($this->selectedStore);

                    // If Settings are like prepare brand name from Configurable product Then
                    if (in_array(\Ced\Cdiscount\Helper\Category::ATTRIBUTE_BRAND_NAME,
                        $requiredAttributeConfType)) {
                        $brandName = $configurableProduct
                            ->getResource()
                            ->getAttribute($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_BRAND_NAME]
                            [self::MAGENTO_ATTRIBUTE_CODE])->setStoreId($this->selectedStore);
                    }

                    // Fetching Brand Name Value
                    if ($brandName &&
                        ($brandName->usesSource() || $brandName->getData('frontend_input') == 'select')) {
                        $brandValue = $brandName
                            ->getSource()
                            ->getOptionText($configurableProduct
                                ->getData($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_BRAND_NAME]
                                [self::MAGENTO_ATTRIBUTE_CODE]));
                        if (is_object($brandValue) && method_exists($brandValue, 'getText')) {
                            $brandValue = $brandValue->getText();
                        }
                    } else {
                        $brandValue = $configurableProduct->
                        getData($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_BRAND_NAME]
                        [self::MAGENTO_ATTRIBUTE_CODE]);
                    }

                    $longLabel = $product
                        ->getData($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_LONG_LABEL]
                        [self::MAGENTO_ATTRIBUTE_CODE]);

                    $description = $product
                        ->getData($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_DESCRIPTION]
                        [self::MAGENTO_ATTRIBUTE_CODE]);

                    $shortLabel = $product
                        ->getData($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_SHORT_LABEL]
                        [self::MAGENTO_ATTRIBUTE_CODE]);

                    $htmlToText = $this->htmlToText->create();

                    $this->data['ProductPackage']['_value']['ProductPackage.Products']
                    ['_value']['ProductCollection']['_value'][$this->key] = [
                        'Product' => [
                            '_attribute' => [
                                'CategoryCode' => $profile->getProfileCategory(),
                                'SellerProductId' => $product
                                    ->getData($requiredAttributes
                                    [\Ced\Cdiscount\Helper\Category::ATTRIBUTE_SELLER_PRODUCT_ID]
                                    [self::MAGENTO_ATTRIBUTE_CODE]),
                                'BrandName' => strip_tags((string)$brandValue),
                                'ShortLabel' => $htmlToText::convert($shortLabel),
                                'LongLabel' => $htmlToText::convert($longLabel),
                                'Description' => $htmlToText::convert($description),
                                'SellerProductFamily' => (string)$configurableProduct->getSku(),
                                'ProductKind' => 'Variant',
                                'Model' => $profile->getModelName(),
                                'Navigation' => isset($categoryPath) ? $categoryPath : $productCategoryName
                                /*'EncodedMarketingDescription' => base64_encode($configurableProduct
                                    ->getData($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_DESCRIPTION]
                                    [self::MAGENTO_ATTRIBUTE_CODE]))*/
                            ],
                            '_value' => [
                                /*'Product.EanList' => [
                                    '_attribute' => [],
                                    '_value' => [
                                        'ProductEan' => [
                                            '_attribute' => [
                                                'Ean' => $product->getData($requiredAttributes
                                                [\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                                                [self::MAGENTO_ATTRIBUTE_CODE])
                                            ],
                                            '_value' => []
                                        ],
                                    ],
                                ],*/
                                'Product.ModelProperties' => [
                                    '_attribute' => [],
                                    '_value' => $modelAttr
                                ]
                            ]
                        ]
                    ];


                    if (isset($requiredAttributes
                            [\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                            [self::MAGENTO_ATTRIBUTE_CODE]) && !empty($requiredAttributes
                        [\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                        [self::MAGENTO_ATTRIBUTE_CODE])) {

                        $this->data['ProductPackage']['_value']['ProductPackage.Products']
                        ['_value']['ProductCollection']
                        ['_value'][$this->key]['Product']['_value']['Product.EanList'] = [
                            '_attribute' => [],
                            '_value' => [
                                'ProductEan' => [
                                    '_attribute' => [
                                        'Ean' => $product
                                            ->getData($requiredAttributes
                                            [\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                                            [self::MAGENTO_ATTRIBUTE_CODE])
                                    ],
                                    '_value' => []
                                ],
                            ],
                        ];
                    }

                    $preparedAttributes = [];

                    // $requiredAttributeConfType => Attributes that are save in configuration to be taken from Config Product
                    if (isset($requiredAttributeConfType) && !empty($requiredAttributeConfType)) {
                        foreach ($requiredAttributeConfType as $rkey => $rValue) {
                            // Skipping Price because it is prepared internally and Brand I've already Prepared Above
                            if ($rValue == 'Price'
                                || $rValue == \Ced\Cdiscount\Helper\Category::ATTRIBUTE_BRAND_NAME) {
                                continue;
                            }
                            // Preparing Attributes From Configurable Product which are selected to be sent from Main
                            $preparedAttributes[$rValue] = $htmlToText::convert($configurableProduct
                                ->getData($requiredAttributes[$rValue]
                                [self::MAGENTO_ATTRIBUTE_CODE]));
                        }
                    }

                    // Assigning the values to the xml
                    if (isset($preparedAttributes) && !empty($preparedAttributes)) {
                        foreach ($preparedAttributes as $prepKay => $prepValue) {
                            $this->data['ProductPackage']['_value']['ProductPackage.Products']
                            ['_value']['ProductCollection']['_value'][$this->key]['Product']
                            ['_attribute'][$prepKay] = $prepValue;
                        }
                    }

                    if (isset($productImages) and !empty($productImages)) {
                        $this->data['ProductPackage']['_value']['ProductPackage.Products']
                        ['_value']['ProductCollection']
                        ['_value'][$this->key]['Product']
                        ['_value']['Product.Pictures'] = [
                            '_attribute' => [],
                            '_value' => $productImages
                        ];
                    }


                    if (isset($optionalAttributes) and !empty($optionalAttributes)) {
                        $optionalAttributes = $this->prepareAttributes($product, $profile,
                            \Ced\Cdiscount\Helper\Profile::ATTRIBUTE_TYPE_OPTIONAL);
                        foreach ($optionalAttributes as $attributeName => $optionalValue) {
                            $this->data['ProductPackage']['_value']
                            ['ProductPackage.Products']['_value']
                            ['ProductCollection']['_value'][$this->key]
                            ['Product']['_attribute']["{$attributeName}"] = $optionalValue;
                        }
                    }

                    $this->key++;
                }

            }
        } catch (\Exception $exception) {
            if ($this->config->getDebugMode() == true) {
                $this->logger->error($exception->getMessage(),
                    ['path' => __METHOD__, 'trace' => $exception->getTraceAsString()]);
            }

        }
    }

    private function prepareAttributes($product, $profile = null, $type = null)
    {
        $data = [];
        /* @var \Ced\Cdiscount\Helper\Profile $profile */
        $mapping = $profile->getAttributes($type);

        if (!empty($mapping)) {

            $globalMapping = $this->attributeCollectionFactory->create()
                ->getFirstItem()->getData();

            foreach ($mapping as $id => $attribute) {
                if (in_array($id, self::SKIPPED_ATTRIBUTES)) {
                    continue;
                }

                $productAttributeValue = "";

                $mappedAttrArray = [];
                if (isset($globalMapping['attribute_name']) && ($globalMapping['attribute_name'] == strtolower($id))) {
                    $mappedAttrData = json_decode($globalMapping['attribute_mappings'], true);
                    if (isset($mappedAttrData) && !empty($mappedAttrData)) {
                        foreach ($mappedAttrData as $attrDatum) {
                            if (isset($attrDatum['magento_size_id']) &&
                                $attrDatum['magento_size_id'] ==
                                $product->getData($attribute[self::MAGENTO_ATTRIBUTE_CODE])) {
                                $mappedAttrArray[$product->getData($attribute[self::MAGENTO_ATTRIBUTE_CODE])]
                                    = $attrDatum['cdiscount_size_id'];
                            }
                        }
                    }
                }

                if (isset($mappedAttrArray) && !empty($mappedAttrArray)) {
                    $productAttributeValue = $mappedAttrArray[$product->getData($attribute[self::MAGENTO_ATTRIBUTE_CODE])];
                } else {
                    if (isset($attribute['default_value']) &&
                        !empty($attribute['default_value'])
                    ) {
                        $productAttributeValue = str_replace("&#39;", "'",
                            $attribute['_value']['default_value']);
                    } else {
                        // case 2: Options
                        // case 2.1: Option mapping value
                        $value = $product->getData($attribute[self::MAGENTO_ATTRIBUTE_CODE]);

                        $attr = $product->getResource()->getAttribute(
                            $attribute[self::MAGENTO_ATTRIBUTE_CODE]
                        )->setStoreId($this->selectedStore);

                        if (isset($attribute['options']) && !empty($attribute['options'])) {
                            if ($attr and ($attr->usesSource() || $attr->getData('frontend_input') == 'select')) {
                                // case 2.2: Option value
                                $valueFromId =
                                    $attr->getSource()
                                        ->getOptionText($product->getData($attribute[self::MAGENTO_ATTRIBUTE_CODE]));
                                if (is_object($valueFromId) && in_array($valueFromId->getText(), $attribute['options'])) {
                                    $productAttributeValue = $valueFromId->getText();
                                } elseif (in_array($valueFromId, $attribute['options'])) {
                                    $productAttributeValue = $valueFromId;
                                }
                            }
                        }

                        if (isset($attribute['option_mapping']) && !empty($attribute['option_mapping'])) {
                            foreach ($attribute['option_mapping'] as $optionsMappedKey => $optionsMappedValue) {
                                if ($optionsMappedKey == $value) {
                                    $productAttributeValue = $optionsMappedValue;
                                    //str_replace("&#39;", "'", $optionsMappedValue);
                                }
                            }

                        } elseif ($attr and ($attr->usesSource() || $attr->getData('frontend_input') == 'select')) {
                            // case 2.2: Option value
                            $productAttributeValue =
                                $attr->getSource()
                                    ->getOptionText($product->getData($attribute[self::MAGENTO_ATTRIBUTE_CODE]));
                            if (is_object($productAttributeValue)) {
                                $productAttributeValue = $productAttributeValue->getText();
                            }
                        } else {
                            $productAttributeValue = $value;
                            //str_replace("&#39;", "'", $value);
                        }
                    }
                }

                if (!empty($productAttributeValue)) {
                    $data[$id] = $productAttributeValue;
                }
            }
        }
        if ($this->debugMode == true && $type == \Ced\Cdiscount\Helper\Profile::ATTRIBUTE_TYPE_MODAL) {
            $this->logger->info('Prepared Data', ['path' => __METHOD__, 'data' => $data]);
        }
        return $data;
    }

    public function saveFailedResponse($url, $failedResponse, $name) {
        $feedModel = $this->feeds->create()->load($name, 'unique_name');
        if ($this->packageName) {
            $packageName = $this->packageName;
        } else {
            $packageName = $feedModel->getSyncUrl();
        }
        $feedModel->addData([
            'feed_id' => 'empty',
            'type' => 'product_creation',
            'feed_response' => json_encode($failedResponse),
            'status' => \Ced\Cdiscount\Model\Source\Feed\Status::FAILURE,
            'feed_file' => '',
            'response_file' => '',
            'feed_created_date' => $this->dateTime->date("Y-m-d"),
            'feed_executed_date' => $this->dateTime->date("Y-m-d"),
            'product_ids' => $this->json->jsonEncode($this->ids),
            'sync_url' => $packageName,
            'unique_name' => $name
        ]);
        $feedModel->save();
    }

    /**
     * @param $response
     * @return bool
     */
    public function saveResponse($response, $type, $name)
    {
        $this->registry->unregister('cdiscount_product_errors');
        $path = 'Undefined';

        if ($type == 'OfferPackage') {
            $type = 'offer_creation';
            $errors = [];
            $path = $this->offerPath;
            $packageStatus = '{}';
            $date = $this->dateTime->date("Y-m-d");
            $statusIntegration = $response->getPackageIntegrationStatus();
            $logMessage = '{}';
            $packageId = $response->getPackageId();
            $errors = $response->getErrorMessage();

            foreach ($response->getOfferLogList() as $reportLog) {

                $date = $reportLog->getLogDate();
                $packageStatus = $reportLog->getOfferIntegrationStatus();

                foreach ($reportLog->getPropertyList() as $offerReportPropertyLog) {
                    $errors['error_code'] = $offerReportPropertyLog->getErrorCode();
                    $errors['log_message'] = $offerReportPropertyLog->getLogMessage();
                    $errors['name'] = ($offerReportPropertyLog->getName() == null ? 'null' :
                        $offerReportPropertyLog->getName());
                    $errors['error_code'] = $offerReportPropertyLog->getErrorCode();
                    $errors['property_log'] = $offerReportPropertyLog->getPropertyError();
                }
            }
        } elseif ($type == 'ProductPackage') {
            $type = 'product_creation';
            $errors = [];
            $path = $this->path;
            $packageStatus = '{}';
            $date = $this->dateTime->date("Y-m-d");
            $statusIntegration = $response->getPackageIntegrationStatus();
            $logMessage = '{}';
            $packageId = $response->getPackageId();
            $errors = $response->getErrorMessage();

            if ($response->isPackageImportHasErrors() || !$response->isPackageImportHasErrors()) {
                foreach ($response->getProductLogList() as $reportLog) {

                    $date = $reportLog->getLogDate();
                    $packageStatus = $reportLog->getProductIntegrationStatus();

                    foreach ($reportLog->getPropertyList() as $productReportPropertyLog) {
                        $errors['error_code'] = $productReportPropertyLog->getErrorCode();
                        $errors['log_message'] = $productReportPropertyLog->getLogMessage();
                        $errors['name'] = ($productReportPropertyLog->getName() == null ? 'null' :
                            $productReportPropertyLog->getName());
                        $errors['property_log'] = $productReportPropertyLog->getPropertyError();
                    }
                }
            } else {
                $packageId = $response->getPackageId();
                $statusIntegration = $response->getPackageIntegrationStatus();
            }
        }
        $status = \Ced\Cdiscount\Model\Source\Feed\Status::SUCCESS;
        if ($type == 'ProductPackage') {
            if ($statusIntegration == 'Rejected') {
                $status = \Ced\Cdiscount\Model\Source\Feed\Status::FAILURE;
            } elseif($statusIntegration == 'IntegrationPending') {
                $status = \Ced\Cdiscount\Model\Source\Feed\Status::INTEGRATION_PENDING;
            }
        } elseif($type == 'OfferPackage') {
            $status = \Ced\Cdiscount\Model\Source\Feed\Status::SUCCESS;
        }
        try {
            $this->registry->register('cdiscount_product_errors', $packageId);
            $feedModel = $this->feeds->create()->load($name, 'unique_name');
            $feedModel->addData([
                'feed_id' => $packageId,
                'type' => $type,
                'feed_response' => $this->json->jsonEncode(
                    ['Body' => json_encode($response->_dataResponse, JSON_PRETTY_PRINT),
                        'Errors' => isset($errors) ? json_encode($errors) : '{}']
                ),
                'status' => $status,
                'feed_file' => $path,
                'response_file' => $logMessage,
                'feed_created_date' => $this->dateTime->date("Y-m-d"),
                'feed_executed_date' => $this->dateTime->date("Y-m-d"),
                'product_ids' => $this->json->jsonEncode($this->ids),
                'sync_url' => $this->packageName,
                'unique_name' => $name
            ]);
            $feedModel->save();
            if (is_array($this->ids) and !empty($this->ids)) {
                foreach ($this->ids as $id) {
                    $product = $this->product->create()->load($id);
                    if (isset($product)) {
                        $product->setCdiscountFeedErrors($this->json->jsonEncode($response));
                        $product->getResource()
                            ->saveAttribute($product, 'cdiscount_feed_errors');
                    }
                }
            }
            return true;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->logger->debug($e->getMessage(), ['path' => __METHOD__]);
            }
        }

        return false;
    }

    /**
     * @param array $ids
     * @return bool
     * @throws \Exception
     */
    public function updatePriceInventory($ids = [])
    {
        $response = false;
        try {
            $timestamp = $this->dateTime->gmtTimestamp();
            $capacity = 0;
            $this->offer = [
                'OfferPackage' => [
                    '_attribute' => [
                        'Name' => "Offer Package " . $timestamp,
                        'PurgeAndReplace' => 'false',
                        'PackageType' => 'StockAndPrice',
                        'xmlns' => 'clr-namespace:Cdiscount.Service.OfferIntegration.Pivot;assembly=Cdiscount.Service.OfferIntegration',
                        'xmlns:x' => 'http://schemas.microsoft.com/winfx/2006/xaml'
                    ],
                    '_value' => [
                        'OfferPackage.Offers' => [
                            '_attribute' => [],
                            '_value' => [
                                'OfferCollection' => [
                                    '_attribute' => [
                                        'Capacity' => $capacity
                                    ],
                                    '_value' => []
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            if ($this->config->proSubscription() == true) {
                $this->offer['OfferPackage']['_value']['OfferPackage.OfferPublicationList'] = [
                    '_attribute' => [],
                    '_value' => [
                        'OfferPublicationList' => [
                            '_attribute' => [
                                'Capacity' => 2,
                            ],
                            '_value' => [
                                0 => [
                                    'PublicationPool' => [
                                        '_attribute' => [
                                            'Id' => 1
                                        ],
                                        '_value' => []
                                    ]
                                ],
                                1 => [
                                    'PublicationPool' => [
                                        '_attribute' => [
                                            'Id' => 16
                                        ],
                                        '_value' => []
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
            } else {
                $this->offer['OfferPackage']['_value']['OfferPackage.OfferPublicationList'] = [
                    '_attribute' => [],
                    '_value' => [
                        'OfferPublicationList' => [
                            '_attribute' => [
                                'Capacity' => 1,
                            ],
                            '_value' => [
                                0 => [
                                    'PublicationPool' => [
                                        '_attribute' => [
                                            'Id' => 1
                                        ],
                                        '_value' => []
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
            }

            if (!empty($ids)) {
                $this->ids = [];
                foreach ($ids as $id) {
                    $product = $this->product->create()
                        ->setStoreId($this->selectedStore)
                        ->load($id);
                    if ($product->getId()) {
                        // Getting product profile

                        // 1.1: Getting Parents and loading parent profile and sending product as child.
                        $productParents = $this->objectManager
                            ->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
                            ->getParentIdsByChild($product->getId());
                        if (!empty($productParents)) {
                            /** @var \Ced\Cdiscount\Helper\Profile $profile */
                            $profile = $this->profileHelper->getProfile($productParents[0]);
                            if (!empty($profile->getId())) {
                                $product = $this->product->create()
                                    ->setStoreId($this->selectedStore)
                                    ->load($productParents[0]);
                            } else {
                                // 1.1.2: Getting product profile id and sending as simple product.
                                /** @var \Ced\Cdiscount\Helper\Profile $profile */
                                $profile = $this->profileHelper->getProfile(
                                    $id,
                                    $product->getData(\Ced\Cdiscount\Helper\Profile::ATTRIBUTE_CODE_PROFILE_ID)
                                );
                                if (empty($profile->getId())) {
                                    continue;
                                }
                            }
                        } else {
                            // 1.2: Getting product profile id and sending as simple product.
                            /** @var \Ced\Cdiscount\Helper\Profile $profile */
                            $profile = $this->profileHelper->getProfile(
                                $id,
                                $product->getData(\Ced\Cdiscount\Helper\Profile::ATTRIBUTE_CODE_PROFILE_ID)
                            );
                            if (empty($profile->getId())) {
                                continue;
                            }
                        }

                        $requiredAttributes = $profile->getRequiredAttributes();
                        // configurable Product
                        if ($product->getTypeId() == 'configurable' &&
                            $product->getVisibility() != 1 &&
                            !in_array($product->getId(), $this->ids)
                        ) {
                            $configurableProduct = $product;
                            $childIds = $configurableProduct->getTypeInstance()->getChildrenIds($product->getId());
                            // getting child products
                            $products = $this->collectionFactory->create()->addAttributeToSelect('*')
                                ->addAttributeToFilter('entity_id', ['in' => $childIds]);
                            //preparing data for child products
                            foreach ($products as $product) {
                                $stock = $this->stockState->getStockQty($product->getId(),
                                    $product->getStore()->getWebsiteId());
                                $price = $this->getPrice($product);
                                $confTypeProdAttr = $this->config->getConfAttrValues();
                                if (in_array('Price', $confTypeProdAttr)) {
                                    $price = $this->getPrice($configurableProduct);
                                }
                                ++$capacity;
                                $this->offer['OfferPackage']['_value']
                                ['OfferPackage.Offers']['_value']['OfferCollection']['_value'][$this->key] =
                                    [
                                        'Offer' => [
                                            '_attribute' => [
                                                'SellerProductId' => $product
                                                    ->getData($requiredAttributes
                                                    [\Ced\Cdiscount\Helper\Category::ATTRIBUTE_SELLER_PRODUCT_ID]
                                                    [self::MAGENTO_ATTRIBUTE_CODE]),
                                                /*'ProductEan' => $product
                                                    ->getData($requiredAttributes
                                                    [\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                                                    [self::MAGENTO_ATTRIBUTE_CODE]),*/
                                                'Stock' => $stock,
                                            ],
                                            '_value' => []
                                        ],
                                    ];

                                if (isset($requiredAttributes
                                    [\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                                    [self::MAGENTO_ATTRIBUTE_CODE]) && !empty($requiredAttributes
                                    [\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                                    [self::MAGENTO_ATTRIBUTE_CODE])) {

                                    $this->offer['OfferPackage']['_value']
                                    ['OfferPackage.Offers']['_value']['OfferCollection']['_value'][$this->key]
                                    ['Offer']['_attribute']['ProductEan'] = $product
                                        ->getData($requiredAttributes
                                        [\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                                        [self::MAGENTO_ATTRIBUTE_CODE]);
                                }

                                if ($this->config->getPriceMapping() && !empty($product->getData($this->config->getPriceMapping()))) {
                                    $this->offer['OfferPackage']['_value']
                                    ['OfferPackage.Offers']['_value']['OfferCollection']['_value']
                                    [$this->key]['Offer']['_attribute']['Price'] =
                                        $product->getData($this->config->getPriceMapping());
                                } else {
                                    if ($price['special_price'] == 0) {
                                        $this->offer['OfferPackage']['_value']
                                        ['OfferPackage.Offers']['_value']
                                        ['OfferCollection']['_value'][$this->key]
                                        ['Offer']['_attribute']['Price'] = $price['price'];
                                    } else {
                                        $this->offer['OfferPackage']['_value']
                                        ['OfferPackage.Offers']['_value']
                                        ['OfferCollection']['_value'][$this->key]
                                        ['Offer']['_attribute']['Price'] = $price['special_price'];
                                    }
                                }
                                $this->ids[$product->getId()] = $product->getId();
                                $this->key++;
                            }
                            $this->ids[$configurableProduct->getId()] = $configurableProduct->getId();
                        } elseif ($product->getTypeId() == 'simple' &&
                            $product->getVisibility() != 1 &&
                            !in_array($product->getId(), $this->ids)
                        ) {
                            $stock = $this->stockState->getStockQty($product->getId(),
                                $product->getStore()->getWebsiteId());
                            $price = $this->getPrice($product);
                            ++$capacity;
                            $this->offer['OfferPackage']['_value']['OfferPackage.Offers']
                            ['_value']['OfferCollection']['_value'][$this->key] =
                                [
                                    'Offer' => [
                                        '_attribute' => [
                                            'SellerProductId' => $product
                                                ->getData($requiredAttributes
                                                [\Ced\Cdiscount\Helper\Category::ATTRIBUTE_SELLER_PRODUCT_ID]
                                                [self::MAGENTO_ATTRIBUTE_CODE]),
                                            /*'ProductEan' => $product
                                                ->getData($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                                                [self::MAGENTO_ATTRIBUTE_CODE]),*/
                                            'Stock' => $stock,
                                        ],
                                        '_value' => []
                                    ],
                                ];

                            if (isset($requiredAttributes
                                    [\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                                    [self::MAGENTO_ATTRIBUTE_CODE]) && !empty($requiredAttributes
                                [\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                                [self::MAGENTO_ATTRIBUTE_CODE])) {

                                $this->offer['OfferPackage']['_value']
                                ['OfferPackage.Offers']['_value']['OfferCollection']['_value'][$this->key]
                                ['Offer']['_attribute']['ProductEan'] = $product
                                    ->getData($requiredAttributes
                                    [\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                                    [self::MAGENTO_ATTRIBUTE_CODE]);

                            }

//                            if ($this->config->getPriceMapping() &&
//                                !empty($product->getData($this->config->getPriceMapping()))) {
//                                die('test');
//                                $this->offer['OfferPackage']['_value']
//                                ['OfferPackage.Offers']['_value']['OfferCollection']['_value']
//                                [$this->key]['Offer']['_attribute']['Price'] =
//                                    $product->getData($this->config->getPriceMapping());
//                            } else {

                                if ($price['special_price'] == 0) {
                                    $this->offer['OfferPackage']['_value']
                                    ['OfferPackage.Offers']['_value']
                                    ['OfferCollection']['_value'][$this->key]
                                    ['Offer']['_attribute']['Price'] = $price['price'];
                                } else {
                                    $this->offer['OfferPackage']['_value']
                                    ['OfferPackage.Offers']['_value']
                                    ['OfferCollection']['_value'][$this->key]
                                    ['Offer']['_attribute']['Price'] = $price['special_price'];
                                }
//                            }
                            $this->ids[$product->getId()] = $product->getId();
                            $this->key++;
                        }
                    }
                }
                $this->offer['OfferPackage']['_value']
                ['OfferPackage.Offers']['_value']
                ['OfferCollection']['_attribute']['Capacity'] = $capacity;
                //print_r($this->generator->create()->arrayToXml($this->offer)->__toString());die('e');
                $this->createOfferPackage($this->offer);
                $response = true;
            }
        } catch (\Exception $exception) {
            if ($this->config->getDebugMode() == true) {
                $this->logger->error($exception->getMessage(), ['path' => __METHOD__, 'ids' => $ids,
                    'offer' => $this->offer]);
            }
        }
        return $response;
    }
    

    /**
     * @param array $ids
     * @return bool
     * @throws \DOMException
     * @throws \Exception
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function updateOffers($ids = [])
    {
        $timestamp = $this->dateTime->gmtTimestamp();
        $capacity = 0;
        $this->offer = [
            'OfferPackage' => [
                '_attribute' => [
                    'Name' => "Offer Package " . $timestamp,
                    'PurgeAndReplace' => 'false',
                    'PackageType' => 'Full',
                    'xmlns' => 'clr-namespace:Cdiscount.Service.OfferIntegration.Pivot;assembly=Cdiscount.Service.OfferIntegration',
                    'xmlns:x' => 'http://schemas.microsoft.com/winfx/2006/xaml'
                ],
                '_value' => [
                    'OfferPackage.Offers' => [
                        '_attribute' => [],
                        '_value' => [
                            'OfferCollection' => [
                                '_attribute' => [
                                    'Capacity' => $capacity
                                ],
                                '_value' => []
                            ]
                        ]
                    ]
                ]
            ]
        ];

        if ($this->config->proSubscription() == true) {
            $this->offer['OfferPackage']['_value']['OfferPackage.OfferPublicationList'] = [
                '_attribute' => [],
                '_value' => [
                    'OfferPublicationList' => [
                        '_attribute' => [
                            'Capacity' => 2,
                        ],
                        '_value' => [
                            0 => [
                                'PublicationPool' => [
                                    '_attribute' => [
                                        'Id' => 1
                                    ],
                                    '_value' => []
                                ]
                            ],
                            1 => [
                                'PublicationPool' => [
                                    '_attribute' => [
                                        'Id' => 16
                                    ],
                                    '_value' => []
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        } else {
            $this->offer['OfferPackage']['_value']['OfferPackage.OfferPublicationList'] = [
                '_attribute' => [],
                '_value' => [
                    'OfferPublicationList' => [
                        '_attribute' => [
                            'Capacity' => 1,
                        ],
                        '_value' => [
                            0 => [
                                'PublicationPool' => [
                                    '_attribute' => [
                                        'Id' => 1
                                    ],
                                    '_value' => []
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        $response = false;
        if (!empty($ids)) {
            $this->ids = [];
            foreach ($ids as $id) {
                $product = $this->product->create()
                    ->setStoreId($this->selectedStore)
                    ->load($id);
                $this->ids = $product->getId();

                // configurable Product
                if ($product->getTypeId() == 'configurable' &&
                    $product->getVisibility() != 1) {
                    $profile = $this->profileHelper->getProfile($product->getId(), $product->getCdiscountProfileId());
                    $productCondition = $profile->getProductStatus();
                    $requiredAttributes = $profile->getRequiredAttributes();
                    $optionalAttributes = $profile->getOptionalAttributes();
                    if (empty($productCondition)) {
                        $productCondition = $this->config->getDefaultProductCondition();
                    }
                    $preprationTime = $this->config->getDefaultPreprationTime();

                    $configurableProduct = $product;
                    $childIds = $configurableProduct->getTypeInstance()->getChildrenIds($product->getId());
                    // getting child products
                    $products = $this->collectionFactory->create()->addAttributeToSelect('*')
                        ->addAttributeToFilter('entity_id', ['in' => $childIds]);
                    //preparing data for child products
                    foreach ($products as $product) {
                        $price = $this->getPrice($product);
                        $confTypeProdAttr = $this->config->getConfAttrValues();
                        if (in_array('Price', $confTypeProdAttr)) {
                            $price = $this->getPrice($configurableProduct);
                        }
                        $stock = $this->stockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
                        ++$capacity;
                        $this->offer['OfferPackage']['_value']['OfferPackage.Offers']
                        ['_value']['OfferCollection']['_value'][$this->key] =
                            [
                                'Offer' => [
                                    '_attribute' => [
                                        'SellerProductId' => $product
                                            ->getData($requiredAttributes
                                            [\Ced\Cdiscount\Helper\Category::ATTRIBUTE_SELLER_PRODUCT_ID]
                                            [self::MAGENTO_ATTRIBUTE_CODE]),
                                        /*'ProductEan' => $product
                                            ->getData($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                                            [self::MAGENTO_ATTRIBUTE_CODE]),*/
                                        'Stock' => $stock,
                                        'PreparationTime' => is_numeric($preprationTime) ? $preprationTime : 1,
                                        'ProductCondition' => is_numeric($productCondition) ? $productCondition : 6,
                                        'IsCDAV' => $this->config->iscdav(),
                                        'EcoPart' => number_format((float)$this->config->getEco(), 1),
                                        'Vat' => number_format((float)$this->config->getVat(), 1),
                                        'DeaTax' => number_format((float)$this->config->getDea(), 1)
                                    ],
                                    '_value' => [
                                        'Offer.ShippingInformationList' => $this->prepareShipmentData()
                                    ]
                                ],
                            ];

                        if (isset($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                            [self::MAGENTO_ATTRIBUTE_CODE]) && !empty($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                            [self::MAGENTO_ATTRIBUTE_CODE])) {

                            $this->offer['OfferPackage']['_value']['OfferPackage.Offers']
                            ['_value']['OfferCollection']['_value'][$this->key]['Offer']['_attribute']
                            ['ProductEan'] = $product
                                ->getData($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                                [self::MAGENTO_ATTRIBUTE_CODE]);

                        }

                        if (!empty($this->config->getPriceMapping()) && !empty($product->getData($this->config->getPriceMapping()))) {
                            $this->offer['OfferPackage']['_value']
                            ['OfferPackage.Offers']['_value']['OfferCollection']['_value']
                            [$this->key]['Offer']['_attribute']['Price'] =
                                $product->getData($this->config->getPriceMapping());
                        } else {
                            if ($price['special_price'] == 0) {
                                $this->offer['OfferPackage']['_value']
                                ['OfferPackage.Offers']['_value']['OfferCollection']['_value']
                                [$this->key]['Offer']['_attribute']['Price'] = $price['price'];
                            } elseif ($price['special_price'] <= $price['price']) {
                                $this->offer['OfferPackage']['_value']
                                ['OfferPackage.Offers']['_value']['OfferCollection']['_value']
                                [$this->key]['Offer']['_attribute']['Price'] = $price['special_price'];

                                $this->offer['OfferPackage']['_value']
                                ['OfferPackage.Offers']['_value']['OfferCollection']['_value']
                                [$this->key]['Offer']['_attribute']['StrikedPrice'] = $price['price'];
                            }
                        }

                        foreach ($optionalAttributes as $optionalAttributeKey => $optionalAttributeValue) {
                            if ($optionalAttributeKey == \Ced\Cdiscount\Helper\Category::ATTRIBUTE_COMMENT) {
                                if (!empty($configurableProduct
                                    ->getData($optionalAttributeValue[self::MAGENTO_ATTRIBUTE_CODE]))) {
                                    $this->offer['OfferPackage']['_value']['OfferPackage.Offers']
                                    ['_value']['OfferCollection']['_value'][$this->key]['Offer']
                                    ['_attribute']['Comment'] = strip_tags($configurableProduct
                                        ->getData($optionalAttributeValue[self::MAGENTO_ATTRIBUTE_CODE]));
                                }
                            }
                            if ($optionalAttributeKey == \Ced\Cdiscount\Helper\Category::ATTRIBUTE_SALE_PRICE) {
                                if (!empty($configurableProduct
                                    ->getData($optionalAttributeValue[self::MAGENTO_ATTRIBUTE_CODE]))) {

                                    $this->offer['OfferPackage']['_value']
                                    ['OfferPackage.Offers']['_value']['OfferCollection']['_value']
                                    [$this->key]['Offer']['_attribute']['Price'] = number_format(round($configurableProduct
                                        ->getData($optionalAttributeValue[self::MAGENTO_ATTRIBUTE_CODE])), 1);
                                }
                            }
                        }
                        $this->key++;
                    }
                } elseif ($product->getTypeId() == 'simple' &&
                    $product->getVisibility() != 1) {
                    $profile = $this->profileHelper->getProfile($product->getId(), $product->getCdiscountProfileId());
                    $productCondition = $profile->getProductStatus();
                    $requiredAttributes = $profile->getRequiredAttributes();
                    $optionalAttributes = $profile->getOptionalAttributes();

                    $preprationTime = $this->config->getDefaultPreprationTime();
                    if (empty($productCondition)) {
                        $productCondition = $this->config->getDefaultProductCondition();
                    }

//                    $StockState = $this->objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface')->getStockStatus($product->getId());
//                    print_r($StockState->getData());
                    $stock = $this->stockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
//                    var_dump($stock);die();
                    ++$capacity;
                    $this->offer['OfferPackage']['_value']['OfferPackage.Offers']['_value']
                    ['OfferCollection']['_value'][$this->key] =
                        [
                            'Offer' => [
                                '_attribute' => [
                                    'SellerProductId' => $product
                                        ->getData($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_SELLER_PRODUCT_ID]
                                        [self::MAGENTO_ATTRIBUTE_CODE]),
                                    /*'ProductEan' => $product
                                        ->getData($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                                        [self::MAGENTO_ATTRIBUTE_CODE]),*/
                                    'Stock' => $stock,
                                    'PreparationTime' => is_numeric($preprationTime) ? $preprationTime : 1,
                                    'ProductCondition' => is_numeric($productCondition) ? $productCondition : 6,
                                    'IsCDAV' => $this->config->iscdav(),
                                    'EcoPart' => number_format((float)$this->config->getEco(), 1),
                                    'Vat' => number_format((float)$this->config->getVat(), 1),
                                    'DeaTax' => number_format((float)$this->config->getDea(), 1)
                                ],
                                '_value' => [
                                    'Offer.ShippingInformationList' => $this->prepareShipmentData()
                                ]
                            ],
                        ];

                    if (isset($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                            [self::MAGENTO_ATTRIBUTE_CODE]) && !empty($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                        [self::MAGENTO_ATTRIBUTE_CODE])) {

                        $this->offer['OfferPackage']['_value']['OfferPackage.Offers']
                        ['_value']['OfferCollection']['_value'][$this->key]['Offer']['_attribute']
                        ['ProductEan'] = $product
                            ->getData($requiredAttributes[\Ced\Cdiscount\Helper\Category::ATTRIBUTE_EAN]
                            [self::MAGENTO_ATTRIBUTE_CODE]);

                    }


//                    if ($this->config->getPriceMapping() && !empty($product->getData($this->config->getPriceMapping()))) {
//                        $this->offer['OfferPackage']['_value']
//                        ['OfferPackage.Offers']['_value']['OfferCollection']['_value']
//                        [$this->key]['Offer']['_attribute']['Price'] =
//                            $product->getData($this->config->getPriceMapping());
//                    } else {
                        $price = $this->getPrice($product);
                        if ($price['special_price'] == 0) {
                            $this->offer['OfferPackage']['_value']
                            ['OfferPackage.Offers']['_value']['OfferCollection']['_value']
                            [$this->key]['Offer']['_attribute']['Price'] = $price['price'];
                        } elseif ($price['special_price'] <= $price['price']) {
                            $this->offer['OfferPackage']['_value']
                            ['OfferPackage.Offers']['_value']['OfferCollection']['_value']
                            [$this->key]['Offer']['_attribute']['Price'] = $price['special_price'];

                            $this->offer['OfferPackage']['_value']
                            ['OfferPackage.Offers']['_value']['OfferCollection']['_value']
                            [$this->key]['Offer']['_attribute']['StrikedPrice'] = $price['price'];
                        }
//                    }

                    foreach ($optionalAttributes as $optionalAttributeKey => $optionalAttributeValue) {
                        if ($optionalAttributeKey == \Ced\Cdiscount\Helper\Category::ATTRIBUTE_COMMENT) {
                            if (!empty($product->getData($optionalAttributeValue[self::MAGENTO_ATTRIBUTE_CODE]))) {
                                $this->offer['OfferPackage']['_value']['OfferPackage.Offers']
                                ['_value']['OfferCollection']['_value'][$this->key]['Offer']
                                ['_attribute']['Comment'] =
                                    strip_tags($product->getData($optionalAttributeValue[self::MAGENTO_ATTRIBUTE_CODE]));
                            }
                        }
                        if ($optionalAttributeKey == \Ced\Cdiscount\Helper\Category::ATTRIBUTE_SALE_PRICE) {
                            if (!empty($product->getData($optionalAttributeValue[self::MAGENTO_ATTRIBUTE_CODE]))) {
                                $this->offer['OfferPackage']['_value']
                                ['OfferPackage.Offers']['_value']['OfferCollection']['_value']
                                [$this->key]['Offer']['_attribute']['Price'] =
                                    number_format(round($product->getData($optionalAttributeValue
                                    [self::MAGENTO_ATTRIBUTE_CODE])), 1);;
                            }
                        }
                    }
                    $this->key++;
                }
            }
            $this->offer['OfferPackage']['_value']['OfferPackage.Offers']['_value']['OfferCollection']
            ['_attribute']['Capacity'] = $capacity;
           // print_r($this->generator->create()->arrayToXml($this->offer)->__toString());die();
            $this->createOfferPackage($this->offer);
            $response = true;
        }
        return $response;
    }

    /**
     * Update Product Status
     * @param string $status
     * @return bool
     */
    public function updateStatus($ids = [], $status = \Ced\Cdiscount\Model\Source\Product\Status::UPLOADED)
    {
        if (!empty($ids) and is_array($ids) and
            in_array($status, \Ced\Cdiscount\Model\Source\Product\Status::STATUS)
        ) {
            $products = $this->product->create()->getCollection()
                ->addAttributeToSelect(['cdiscount_product_status'])
                ->addAttributeToFilter('entity_id', ['in' => $ids]);
            foreach ($products as $product) {
                $product->setData('cdiscount_product_status', $status);
                $product->getResource()->saveAttribute($product, 'cdiscount_product_status');
            }
            return true;
        }
        return false;
    }

    /**
     * Check if configurations are valid
     * @return boolean
     */
    public function checkForConfiguration()
    {
        return $this->config->isValid();
    }
}
