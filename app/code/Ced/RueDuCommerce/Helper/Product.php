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
 * @package     Ced_RueDuCommerce
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RueDuCommerce\Helper;

/**
 * Directory separator shorthand
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * Class Data For RueDuCommerce Authenticated Seller Api
 * @package Ced\RueDuCommerce\Helper
 */
class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ATTRIBUTE_TYPE_SKU = 'sku';

    const ATTRIBUTE_TYPE_NORMAL = 'normal';

    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

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

    /**
     * File Manager
     * @var $fileIo
     */
    public $fileIo;

    /**
     * RueDuCommerce Logger
     * @var \Ced\RueDuCommerce\Helper\Logger
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
     * @var \Ced\RueDuCommerce\Model\FeedsFactory
     */
    public $feeds;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $product;

    /**
     * @var string
     */
    public $apiAuthKey;
    public $urlBuilder;
    public $fulfillmentLagTime;
    public $ids = [];
    public $data = [];
    public $offerData = [];
    public $key = 0;
    public $mpn = '';
    public $rueducommerce;
    public $stockState;
    public $debugMode;

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
     * @param \RueDuCommerceSdk\ProductFactory $rueducommerce
     * @param \Ced\RueDuCommerce\Model\FeedsFactory $feedsFactory
     * @param Config $config
     * @param Logger $logger
     * @param Profile $profile
     * @param \RueDuCommerceSdk\Api\Config $rueducommerceConfig
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
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Message\ManagerInterface $manager,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \RueDuCommerceSdk\ProductFactory $rueducommerce,
        \Ced\RueDuCommerce\Model\FeedsFactory $feedsFactory,
        \Ced\RueDuCommerce\Helper\Config $config,
        \Ced\RueDuCommerce\Helper\Logger $logger,
        \Ced\RueDuCommerce\Helper\Profile $profile,
        \RueDuCommerceSdk\Core\Config $rueducommerceConfig,
        \Magento\Catalog\Model\ResourceModel\Product\Action $productAction,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory $configfactory,
        \Magento\Framework\App\Cache $cache,
        \Magento\Framework\Session\SessionManagerInterface $session
    ) {
        parent::__construct($context);
        $this->objectManager = $objectManager;
        $this->urlBuilder = $url;
        $this->json = $json;
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
        $this->rueducommerce = $rueducommerce;
        $this->profileHelper = $profile;
        $this->feeds = $feedsFactory;
        $this->config = $config;
        $this->_prodAction = $productAction;
        $this->prodCollection = $productFactory;
        $this->configProduct = $configfactory;
        $this->cache = $cache;
        $this->session = $session;
        $this->selectedStore = $config->getStore();
        $this->debugMode = $rueducommerceConfig->getDebugMode();
    }

    /**
     * Load File
     * @param string $path
     * @param string $code
     * @return mixed|string
     * @deprecated
     */
    public function loadFile($path, $code = '')
    {
        try {
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
                            $this->logger->error('Load File', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                        }
                    }
                }
            }
            return false;
        } catch (\Exception $e) {
            $this->logger->error('Load File', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    /**
     * Create Json/Xml File
     * @param string|[] $data associative array to be converted into json or xml file
     * @param string|[] $params
     * 'type': file type json or xml, default json,
     * 'name': file name,
     * 'path': path to save file, default 'var/rueducommerce'
     * 'code': directory code, default 'var'
     * @return boolean
     * @deprecated
     */
    public function createFile($data, $params = [])
    {
        $type = 'json';
        $timestamp = $this->objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
        $name = 'rueducommerce_' . $timestamp->gmtTimestamp();
        $path = 'rueducommerce';
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
            $this->logger->error('Create File', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }

        return true;
    }

    /**
     * Create rueducommerce directory in the specified root directory.
     * used for storing json/xml files to be synced.
     * @param string $name
     * @param string $code
     * @return array|string
     */
    public function createDir($name = 'rueducommerce', $code = 'var')
    {
        $path = $this->directoryList->getPath($code) . "/" . $name;
        if ($this->fileIo->fileExists($path)) {
            return ['status' => true, 'path' => $path, 'action' => 'dir_exists'];
        } else {
            try {
                $this->fileIo->mkdir($path, 0775, true);
                return ['status' => true, 'path' => $path, 'action' => 'dir_created'];
            } catch (\Exception $e) {
                return $code . '/' . $name . "Directory Creation Failed.";
            }
        }
    }

    /**
     * Create/Update Product on RueDuCommerce
     * @param [] $ids
     * @return bool
     */
    public function createProducts($ids = [])
    {
        try {
            $response = false;
            $ids = $this->validateAllProducts($ids);
            if (!empty($ids['simple']) or !empty($ids['configurable'])) {
                $this->ids = [];
                $this->key = 0;
                $this->data = [];
                $this->prepareSimpleProducts($ids['simple']);

                $this->prepareConfigurableProducts($ids['configurable']);
                $response = $this->rueducommerce->create(['config' => $this->config->getApiConfig()])->createProduct($this->data);

                if ($response && (isset($response['feed_id']) || isset($response[0]['feed_id']))
                ) {
                    $this->updateStatus($this->ids, \Ced\RueDuCommerce\Model\Source\Product\Status::UPLOADED);
                } else {
                    $this->updateStatus($this->ids, \Ced\RueDuCommerce\Model\Source\Product\Status::INVALID);
                }
                $response = $this->saveResponse($response);
                return $response;
            }
            return $response;
        } catch (\Exception $e) {
            $this->logger->error('Create Product', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    /**
     * Create/Update Product on RueDuCommerce
     * @param [] $ids
     * @return bool
     */
    public function updateProducts($ids = [])
    {
        try {
            $response = false;
            $ids = $this->validateAllProducts($ids);
            if (!empty($ids['simple']) or !empty($ids['configurable'])) {
                $this->ids = [];
                $this->key = 0;
                $this->data = [];
                $this->prepareSimpleProducts($ids['simple']);
                $this->prepareConfigurableProducts($ids['configurable']);
                $response = $this->rueducommerce->create(['config' => $this->config->getApiConfig()])->updateProduct($this->data);
                if ($response and
                    $response->getStatus() == \RueDuCommerceSdk\Api\Response::REQUEST_STATUS_SUCCESS and
                    empty($response->getError())
                ) {
                    $this->updateStatus($this->ids, \Ced\RueDuCommerce\Model\Source\Product\Status::LIVE);
                } else {
                    $this->updateStatus($this->ids, \Ced\RueDuCommerce\Model\Source\Product\Status::INVALID);
                }
                $response = $this->saveResponse($response);
                return $response;
            }
            return $response;
        } catch (\Exception $e) {
            $this->logger->error('Validate/Create/Update Product', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    /**
     * Validate All Products
     * @param array $ids
     * @return array
     */
    public function validateAllProducts($ids = [])
    {
        try {
            $validatedProducts = [
                'simple' => [],
                'configurable' => [],
            ];
            $this->ids = [];

            foreach ($ids as $id) {
                $product = $this->product->create()->load($id)->setStoreId($this->selectedStore);

                // get profile
                $productParents = $this->objectManager
                    ->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
                    ->getParentIdsByChild($product->getId());
                if (!empty($productParents)) {
                    $profile = $this->profileHelper->getProfile($productParents[0]);
                    if (!empty($profile)) {
                        $product = $this->product->create()->load($productParents[0])
                            ->setStoreId($this->selectedStore);
                    } else {
                        $validatedProducts['errors'][$product->getSku()] =
                            'Please assign product to a profile and try again.';
                        $profile = $this->profileHelper->getProfile($id);
                    }
                } else {
                    $profile = $this->profileHelper->getProfile($id);
                    if (empty($profile)) {
                        $validatedProducts['errors'][$product->getSku()] =
                            'Please assign product to a profile and try again.';
                        continue;
                    }
                }

                // case 1 : for config products
                if ($product->getTypeId() == 'configurable' &&
                    $product->getVisibility() != 1
                ) {
                    $uploadConfigAsSimple = false;
                    $uploadAsSimple = $this->config->getConfigAsSimple();
                    $skipAttributes = $this->config->getSkipValidationAttributes();
                    $skipAttributes = array_flip($skipAttributes);
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
                    $errors = [
                        $sku => [
                            'sku' => $sku,
                            'id' => $configurableProduct->getId(),
                            'url' => $this->urlBuilder
                                ->getUrl('catalog/product/edit', ['id' => $configurableProduct->getId()]),
                            'errors' => []
                        ]
                    ];
                    $rueducommerceRequiredAttributes = $profile->getRequiredAttributes();
                    $rueducommerceVariantAttributes = $profile->getAttributes(self::ATTRIBUTE_TYPE_SKU);


                    //common attributes check start
                    $commonErrors = [];
                    foreach ($profile->getRequiredAttributes(self::ATTRIBUTE_TYPE_NORMAL) as $attributeId => $validationAttribute) {
                        $value = $configurableProduct->getData($validationAttribute['magento_attribute_code']);
                        if (!isset($value) || empty($value)) {
                            $commonErrors[$attributeId] = 'Common required attribute empty.';
                        }
                    }
                    if (!empty($commonErrors)) {
                        $errors[$sku]['errors'][] = $commonErrors;
                    }
                    //common attributes check end.

                    // variant attribute mapping check start.
                    $unmappedVariantAttribute = [];
                    $mappedVariantAttributes = [];

                    $magentoAttrbuteCode = array_column($rueducommerceVariantAttributes, 'name', 'magento_attribute_code');
                    foreach ($magentoVariantAttributes as $code) {
                        //check mapping
                        if (!isset($magentoAttrbuteCode[$code])) {
                            if (!isset($skipAttributes[$code])) {
                                $unmappedVariantAttribute[] = $code;
                            }
                        } else {
                            $mappedVariantAttributes[] = $code;
                        }
                    }
                    $rueducommerceVariantAttributesValues = [];
                    foreach ($rueducommerceVariantAttributes as $attributeId => $variantAttribute) {
                        if (isset($variantAttribute['magento_attribute_code']) and in_array($variantAttribute['magento_attribute_code'], $mappedVariantAttributes)) {
                            $rueducommerceVariantAttributesValues[$variantAttribute['magento_attribute_code']] =
                                $variantAttribute;
                        }
                    }
                    // variant attribute mapping check end.

                    $key = 0;
                    if (empty($products))
                        $errors[$configurableProduct->getSku()]['errors'][]['Configurable'] = ['Product has no variation in it.'];

                    if ((!isset($rueducommerceVariantAttributes['variant-size-value']) && !isset($rueducommerceVariantAttributes['variant-colour-value'])) && $uploadAsSimple == '1') {
                        $uploadConfigAsSimple = true;
                    }

                    foreach ($products as $product) {
                        $modifiedParentSKU = $sku;
                        $errors[$product->getSku()] = [
                            'sku' => $product->getSku(),
                            'id' => $product->getId(),
                            'url' => $this->urlBuilder
                                ->getUrl('catalog/product/edit', ['id' => $product->getId()]),
                            'errors' => []
                        ];

                        $product = $this->product->create()->load($product->getId())
                            ->setStoreId($this->selectedStore);
                        $productId = $this->validateProduct($product->getId(), $product, $profile, $parentId);
                        $sizeValue = '';
                        if (isset($rueducommerceVariantAttributes['variant-size-value']['magento_attribute_code'])) {
                            $sizeValue = $product->getData($rueducommerceVariantAttributes['variant-size-value']['magento_attribute_code']);
                        }
                        $colorValue = '';
                        if (isset($rueducommerceVariantAttributes['variant-colour-value']['magento_attribute_code'])) {
                            $colorValue = $product->getData($rueducommerceVariantAttributes['variant-colour-value']['magento_attribute_code']);
                        }
                        if (((!isset($rueducommerceVariantAttributes['variant-size-value']) || (isset($rueducommerceVariantAttributes['variant-size-value']) && empty($sizeValue))) && (!isset($rueducommerceVariantAttributes['variant-colour-value']) || (isset($rueducommerceVariantAttributes['variant-colour-value']) && empty($colorValue)))) && $uploadAsSimple != '1') {
                            $errors[$product->getSku()]['errors'][]['variant-size/color-value'] = 'Variant Size/Colour Value is not mappped with size/colour or mapped attribute has no value. You can skip this validation from CONFIGURATION.';
                        }
                        if (empty($sizeValue) && $uploadAsSimple == '1' && $uploadConfigAsSimple === false) {
                            $uploadConfigAsSimple = true;
                        }

                        // variant attribute option value check start.
                        foreach ($mappedVariantAttributes as $mappedVariantAttribute) {
                            if (isset($rueducommerceVariantAttributesValues[$mappedVariantAttribute]['options'])) {
                                $valueId = $product->getData($mappedVariantAttribute);
                                $value = "";
                                $defaultValue = "";

                                //case 1: default value
                                if (isset($rueducommerceVariantAttributesValues[$mappedVariantAttribute]['default_value']) and
                                    !empty($rueducommerceVariantAttributesValues[$mappedVariantAttribute]['default_value'])
                                ) {
                                    $defaultValue =
                                        $rueducommerceVariantAttributesValues[$mappedVariantAttribute]['default_value'];
                                }

                                //case 3: magento attribute option value
                                $attr = $product->getResource()->getAttribute($mappedVariantAttribute);
                                if ($attr && ($attr->usesSource() || $attr->getData('frontend_input') == 'select')) {
                                    $value = $attr->getSource()->getOptionText($valueId);
                                    if (is_object($value)) {
                                        $value = $value->getText();
                                    }
                                }
                            }
                        }

                        foreach ($magentoVariantAttributes as $code) {
                            if (isset($magentoAttrbuteCode[$code]) && ($magentoAttrbuteCode[$code] == 'variant-size-value' || $magentoAttrbuteCode[$code] == 'variant-colour-value')) {
                                continue;
                            }
                            $modifiedParentSKU .= '_' . $product->getData($code);
                        }

                        // variant attribute option value check end.

                        if (isset($productId['id']) and
                            empty($errors[$sku]['errors']) and
                            empty($errors[$product->getSku()]['errors'])
                        ) {
                            //Check if all mappedAttributes are mapped

                            if (empty($unmappedVariantAttribute)) {
                                $validatedProducts['configurable'][$parentId][$product->getId()]['id'] = $productId['id'];
                                $validatedProducts['configurable'][$parentId][$product->getId()]['type'] = 'configurable';
                                $validatedProducts['configurable'][$parentId][$product->getId()]['variantid'] = $modifiedParentSKU;
                                $validatedProducts['configurable'][$parentId][$product->getId()]['parentid'] = $parentId;
                                $validatedProducts['configurable'][$parentId][$product->getId()]['variantattr'] =
                                    $mappedVariantAttributes;
                                $validatedProducts['configurable'][$parentId][$product->getId()]['variantattrmapped'] =
                                    $rueducommerceVariantAttributesValues;
                                $validatedProducts['configurable'][$parentId][$product->getId()]['isprimary'] = 'false';
                                $validatedProducts['configurable'][$parentId][$product->getId()]['isprimary'] = 'false';
                                $validatedProducts['configurable'][$parentId][$product->getId()]['category'] =
                                    $profile->getProfileCategory();
                                $validatedProducts['configurable'][$parentId][$product->getId()]['profile_id'] =
                                    $profile->getId();
                                $validatedProducts['configurable'][$parentId][$product->getId()]['upload_as_simple'] = ($uploadConfigAsSimple == true) ? 'true' : 'false';
                                if ($key == 0) {
                                    $validatedProducts['configurable'][$parentId][$product->getId()]['isprimary'] = 'true';
                                    $key = 1;
                                }
                                $product->setData('rueducommerce_validation_errors', $this->json->jsonEncode(array('valid')));
                                $product->getResource()->saveAttribute($product, 'rueducommerce_validation_errors');
                                continue;
                            } else {
                                $errorIndex = implode(", ", $unmappedVariantAttribute);
                                $errors[$product->getSku()]['errors'][][$errorIndex] = [
                                    'Configurable attributes not mapped.'];
                            }
                        } elseif (isset($productId['errors'])) {
                            $errors[$product->getSku()]['errors'][] = $productId['errors'];

                            if (empty($mappedVariantAttributes)) {
                                $errorIndex = implode(", ", $unmappedVariantAttribute);
                                $errors[$product->getSku()]['errors'][][$errorIndex] = [
                                    'Configurable attributes not mapped.'];
                            }
                            $childError = [];
                            if (isset($productId['errors']['sku']) && isset($productId['errors']['errors'])) {
                                $childError[$productId['errors']['sku']]['errors'][] = $productId['errors']['errors'];

                                $product->setRueducommerceValidationErrors($this->json->jsonEncode($childError));
                                $product->getResource()->saveAttribute($product, 'rueducommerce_validation_errors');
                            }

                        }
                    }

                    if (!empty($errors)) {
                        if (!empty($unmappedVariantAttribute)) {
                            $errorIndex = implode(", ", $unmappedVariantAttribute);
                            $errors[$configurableProduct->getSku()]['errors'][][$errorIndex] = [
                                'Configurable attributes not mapped.'];
                        }
                        $errorsInRegistry = $this->registry->registry('rueducommerce_product_validaton_errors');
                        $this->registry->unregister('rueducommerce_product_validaton_errors');
                        $this->registry->register(
                            'rueducommerce_product_validaton_errors',
                            is_array($errorsInRegistry) ? array_merge($errorsInRegistry, $errors) : $errors
                        );

                        $configurableProduct->setRueducommerceValidationErrors($this->json->jsonEncode($errors));
                        $configurableProduct->getResource()
                            ->saveAttribute($configurableProduct, 'rueducommerce_validation_errors');
                    } else {
                        $configurableProduct->setRueducommerceValidationErrors('["valid"]');
                        $configurableProduct->getResource()
                            ->saveAttribute($configurableProduct, 'rueducommerce_validation_errors');
                    }
                } elseif (($product->getTypeId() == 'simple') && ($product->getVisibility() != 1)) {

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
                    } elseif (isset($productId['errors']) and is_array($productId['errors'])) {
                        $errors[$product->getSku()] = [
                            'sku' => $product->getSku(),
                            'id' => $product->getId(),
                            'url' => $this->urlBuilder
                                ->getUrl('catalog/product/edit', ['id' => $product->getId()]),
                            'errors' => $productId['errors']
                        ];
                        $errorsInRegistry = $this->registry->registry('rueducommerce_product_validaton_errors');
                        $this->registry->unregister('rueducommerce_product_validaton_errors');
                        $this->registry->register(
                            'rueducommerce_product_validaton_errors',
                            is_array($errorsInRegistry) ? array_merge($errorsInRegistry, $errors) :
                                $errors
                        );
                    }
                }
            }

            return $validatedProducts;
        } catch (\Exception $e) {
            $this->logger->error('Validate Product', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    /**
     * Validate product for availability of required rueducommerce product attribute data
     * @param $id
     * @param null $product
     * @param null $profile
     * @param null $parentId
     * @return bool
     */
    public function validateProduct($id, $product = null, $profile = null, $parentId = null)
    {
        try {
            $validatedProduct = false;

            //if product object is not passed, then load in case of Simple product
            if ($product == null) {
                $product = $this->product->create()
                    ->load($id)
                    ->setStoreId($this->selectedStore);
            }

            //if profile is not passed, get profile
            if ($profile == null) {
                $profile = $this->profileHelper->getProfile($product->getId());
            }

            $profileId = $profile->getId();
            $sku = $product->getSku();
            $productArray = $product->toArray();
            $errors = [];
            //Case 1: Profile is Available
            if (isset($profileId) and $profileId != false) {
                $category = $profile->getProfileCategory();
                $requiredAttributes = $profile->getRequiredAttributes();
                foreach ($requiredAttributes as $rueducommerceAttributeId => $rueducommerceAttribute) {

                    if ($rueducommerceAttributeId === 'product-reference-value') {
                        if (isset($productArray[$rueducommerceAttribute['magento_attribute_code']]) and
                            !empty($productArray[$rueducommerceAttribute['magento_attribute_code']])
                        ) {
                            $refType = (isset($requiredAttributes['product-reference-type']['magento_attribute_code'])
                                && $requiredAttributes['product-reference-type']['magento_attribute_code'] == 'default')
                                ? $requiredAttributes['product-reference-type']['default'] : (isset($productArray[$requiredAttributes['product-reference-type']['magento_attribute_code']]) ? $productArray[$requiredAttributes['product-reference-type']['magento_attribute_code']] : '');
                            $flag = $this->validateProductId(
                                $productArray[$rueducommerceAttribute['magento_attribute_code']],
                                $refType
                            );
                            if (!$flag) {
                                $errors[$rueducommerceAttributeId] = 'Invalid Product Identifier(EAN/UPC/ISBN/MPN) Digits Or Invalid Reference Type';
                            }
                            continue;
                        } else {
                            $errors[$rueducommerceAttributeId] = 'Invalid Product Identifier(EAN/UPC/ISBN/MPN) Digits Or Empty';
                            continue;
                        }
                    }

                    $skippedAttribute = [];
                    if (in_array($rueducommerceAttributeId, $skippedAttribute)) {
                        // Validation case 1 skip some attributes that are not to be validated.
                        continue;
                    } elseif (!isset($productArray[$rueducommerceAttribute['magento_attribute_code']])
                        || empty($productArray[$rueducommerceAttribute['magento_attribute_code']]) and
                        empty($rueducommerceAttribute['default'])
                    ) {
                        // Validation case 2 Empty or blank value check
                        $errors["$rueducommerceAttributeId"] = "Required attribute empty or not mapped. [{$rueducommerceAttribute['magento_attribute_code']}]";
                    } elseif (isset($rueducommerceAttribute['options']) and
                        !empty($rueducommerceAttribute['options'])
                    ) {
                        $valueId = $product->getData($rueducommerceAttribute['magento_attribute_code']);
                        $value = "";
                        $defaultValue = "";
                        // Case 2: default value from profile
                        if (isset($rueducommerceAttribute['default']) and
                            !empty($rueducommerceAttribute['default'])
                        ) {
                            $defaultValue = $rueducommerceAttribute['default'];
                        }
                        // Case 3: magento attribute option value
                        $attr = $product->getResource()->getAttribute($rueducommerceAttribute['magento_attribute_code']);
                        if ($attr && ($attr->usesSource() || $attr->getData('frontend_input') == 'select')) {
                            $value = $attr->getSource()->getOptionText($valueId);
                            if (is_object($value)) {
                                $value = $value->getText();
                            }
                        }
                        // order of check: default value > option mapping > default magento option value
                        if (!isset($rueducommerceAttribute['options'][$defaultValue]) &&
                            !isset($rueducommerceAttribute['option_mapping'][$valueId]) &&
                            !isset($rueducommerceAttribute['options'][$value])
                        ) {
                            $errors["$rueducommerceAttributeId"] = "RueDuCommerce attribute: [" . $rueducommerceAttribute['name'] .
                                "] mapped with [" . $rueducommerceAttribute['magento_attribute_code'] .
                                "] has invalid option value: <b> " . json_encode($value) . "/" . json_encode($valueId) .
                                "</b> or default value: " . json_encode($defaultValue);
                        }
                    }
                }
                $additionalImages = $this->prepareImages($product, 0);

                if (count($additionalImages) == 0 && $parentId) {
                    $configurableProduct = $this->product->create()->load($parentId)
                        ->setStoreId($this->selectedStore);
                    $additionalImages = $this->prepareImages($configurableProduct, 0);
                }

                if (count($additionalImages) == 0) {
                    $errors['Image'] = 'One image must be above 450 x 367 px';
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
                    $product->setRueducommerceValidationErrors($this->json->jsonEncode($e));
                    $product->getResource()
                        ->saveAttribute($product, 'rueducommerce_validation_errors');
                } else {
                    // insert product id for status update.
                    $this->ids[] = $product->getId();

                    $product->setData('rueducommerce_validation_errors', '["valid"]');
                    $product->getResource()
                        ->saveAttribute($product, 'rueducommerce_validation_errors');
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
                            "Profile not found" => "Product or Parent Product is not mapped in any rueducommerce profile"
                        ]
                ];
                $validatedProduct['errors'] = $errors;
                $errors = $this->json->jsonEncode([$errors]);
                $product->setData('rueducommerce_validation_errors', $errors);
                $product->getResource()
                    ->saveAttribute($product, 'rueducommerce_validation_errors');
            }
            return $validatedProduct;
        } catch (\Exception $e) {
            $this->logger->error('Validate Product', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    private function prepareSimpleProducts($ids = [])
    {
        try {
            $product_array = [];
            foreach ($ids as $key => $id) {
                $product = $this->product->create()->load($id['id']);
                $profile = $this->profileHelper->getProfile($product->getId(), $id['profile_id']);
                $category = $this->profileHelper->getProfileCategory();
                $this->ids[] = $product->getId();
                $price = $this->getPrice($product);
                $attributes = $this->prepareAttributes(
                    $product,
                    $profile
                );

                $attrKey = 0;
                /*$requiredArray = array('internal-sku', 'title', 'product-reference-type',
                            'product-reference-value', 'product-description', 'brand');*/
                $requiredArray = array('sku', 'product-id', 'product-id-type',
                    'description', 'internal-description', 'price', 'price-additional-info', 'quantity',
                    'min-quantity-alert', 'state', 'available-start-date', 'available-end-date', 'logistic-class',
                    'discount-price', 'discount-start-date', 'discount-end-date', 'leadtime-to-ship', 'update-delete',
                    'club-rueducommerce-eligible', 'tax-au', 'best-before-date', 'expiry-date',
                    'sales-price-1', 'sales-price-2', 'sales-price-3', 'sales-start-date-1', 'sales-start-date-2', 'sales-start-date-3',
                    'sales-end-date-1', 'sales-end-date-2', 'sales-end-date-3', 'sales-flash-price', 'sales-flash-start-date', 'sales-flash-end-date',
                    'copyright-tax', 'corepile-tax', 'dea-tax', 'deb-tax', 'deee-tax', 'ecoemballage-tax', 'ecofolio-tax', 'ecomobilier-tax',
                    'merchant-warranty', 'grade');

                foreach ($attributes as $attKey => $attrValue) {

                    if (!in_array($attKey, $requiredArray)) {
                        if(in_array($attKey, ['brand'])) {
                            $attrValue = strtolower($attrValue);
                        }
                        $product_array[$attrKey] = array(
                            'attribute' => array(
                                '_attribute' => array(),
                                '_value' => array(
                                    'code' => (string)$attKey,
                                    'value' => (string)$attrValue
                                )
                            )
                        );
                        $attrKey++;
                    }
                }

                $product_array[$attrKey] = array(
                    'attribute' => array(
                        '_attribute' => array(),
                        '_value' => array(
                            'code' => 'category',
                            'value' => $category
                        )
                    )
                );

                $additionalImages = $this->prepareImages($product, $attrKey);
                $pdata = array_merge($product_array, $additionalImages);
                $this->data[$this->key]['product'] = array(
                    '_attribute' => array(),
                    '_value' => $pdata

                );
                $this->data = array_values($this->data);
                $this->key++;
            }
        } catch (\Exception $e) {
            $this->logger->error('Create Product', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    //@TODO ADD input type check
    /**
     * Prepare Attributes for Products
     * @param $product
     * @param null $profile
     * @param $type
     * @return array
     */
    private function prepareAttributes($product, $profile = null, $type = "normal")
    {
        try {
            $data = [];
            $mapping = $profile->getAttributes($type);
            if (!empty($mapping)) {
                foreach ($mapping as $id => $attribute) {
                    $productAttributeValue = "";
                    // case 1: default value
                    if (isset($attribute['default']) and
                        !empty($attribute['default'])
                    ) {
                        $productAttributeValue = str_replace("&#39;", "'", $attribute['default']);
                    } else {
                        // case 2: Options
                        // case 2.1: Option mapping value
                        $value = $product->getData($attribute['magento_attribute_code']);
                        if($attribute['magento_attribute_code'] == 'quantity_and_stock_status') {
                            $value = isset($value['qty']) ? (string)$value['qty'] : (string) $value;
                        }
                        $attr = $product->getResource()->getAttribute(
                            $attribute['magento_attribute_code']
                        );

                        if (isset($attribute['option_mapping'][$value]) and
                            is_array($attribute['option_mapping'])
                        ) {
                            $productAttributeValue =
                                str_replace("&#39;", "'", $attribute['option_mapping'][$value]);
                        } elseif ($attr and ($attr->usesSource() || $attr->getData('frontend_input') == 'select')) {
                            // case 2.2: Option value
                            $productAttributeValue =
                                $attr->getSource()
                                    ->getOptionText($product->getData($attribute['magento_attribute_code']));
                            if($attribute['magento_attribute_code'] == 'quantity_and_stock_status') {
                                $productAttributeValue = isset($value['qty']) ? (string)$value['qty'] : (string) $value;
                            }
                            if (is_object($productAttributeValue)) {
                                $productAttributeValue = $productAttributeValue->getText();
                            }
                        } else {
                            $productAttributeValue =
                                str_replace("&#39;", "'", $value);
                        }
                    }

                    if ($productAttributeValue != null) {
                        if ($attribute['inputType'] == 'richText') {
                            $data[$id] = '<![CDATA[' . $productAttributeValue . ']]>';
                        } else {
                            $data[$id] = $productAttributeValue;
                        }
                    }
                }
            }

            if ($type == 'sku') {
                $data['quantity'] = (string)$this->stockState->getStockQty(
                    $product->getId(),
                    $product->getStore()->getWebsiteId()
                );
                $price = $this->getPrice($product);
                $data['price'] = $price['price'];
            }
            return $data;
        } catch (\Exception $e) {
            $this->logger->error('Validate/Create Product', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    /**
     * @param $productObject
     * @return array
     */
    public function getPrice($productObject, $attrValue = null)
    {
        $splprice = (float)$productObject->getFinalPrice();
        $price = (float)$productObject->getPrice();
        if($attrValue != '') {
            $splprice = $price = $attrValue;
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
                    $this->_logger->debug(" RueDuCommerce: Product Helper: getRueDuCommercePrice() : " . $e->getMessage());
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
     * Prepare images
     * @param Object $product
     * @return string|array
     */
    private function prepareImages($product, $index, $configProduct = null)
    {
        try {
            $mergeImages = $this->config->getMergeParentImages();
            $productImages = $product->getMediaGalleryImages();
            $mainImage = $product->getData('image');
            $images = [];
            $index = $index + 1;
            if ($productImages->getSize() > 0) {
                $image_index = 1;
                foreach ($productImages as $key => $image) {

                    if ($image_index > 6) {
                        break;
                    }
                    if ($image && $image->getUrl()) {
                        if ($handle = fopen($image->getUrl(), 'r')) {
                            list($prodWidth, $prodHeight) = getimagesize($image->getUrl());
                            if ($prodWidth > 450 && $prodHeight > 367) {
                                $images[$index] = array(
                                    'attribute' => array(
                                        '_attribute' => array(),
                                        '_value' => array(
                                            'code' => 'media_url_' . $image_index,
                                            'value' => $image->getUrl(),
                                        )
                                    )
                                );
                                $image_index++;
                            }
                        }
                    }
                    $index++;
                }
            }
            if($configProduct != null && $mergeImages == '1'){
                $productImages = $configProduct->getMediaGalleryImages();
                if ($productImages->getSize() > 0) {
                    $image_index = (isset($image_index)) ? $image_index : 1;
                    foreach ($productImages as $key => $image) {

                        if ($image_index > 6) {
                            break;
                        }
                        if ($image && $image->getUrl()) {
                            if ($handle = fopen($image->getUrl(), 'r')) {
                                list($prodWidth, $prodHeight) = getimagesize($image->getUrl());
                                if ($prodWidth > 450 && $prodHeight > 367) {
                                    $images[$index] = array(
                                        'attribute' => array(
                                            '_attribute' => array(),
                                            '_value' => array(
                                                'code' => 'media_url_' . $image_index,
                                                'value' => $image->getUrl(),
                                            )
                                        )
                                    );
                                    $image_index++;
                                }
                            }
                        }
                        $index++;
                    }
                }
            }
            return $images;
        } catch (\Exception $e) {
            $this->logger->error('Validate/Create Product Images Prepare', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    private function prepareConfigurableProducts($ids = [])
    {
        try {
            $fromParentAttrs = $this->config->getFromParentAttributes();
            foreach ($ids as $parentId => $products) {
                $configurableProduct = $this->product->create()->load($parentId);
                $this->ids[] = $configurableProduct->getId();
                $firstProduct = reset($products);
                $profile = $this->profileHelper->getProfile($configurableProduct->getId(), $firstProduct['profile_id']);
                $category = $profile->getProfileCategory();

                // Adding Variant Items

                $skus = [];
                foreach ($products as $productId => $id) {
                    $product_array = [];
                    $productIds[] = $id;
                    $product = $this->product->create()->load($productId);
                    $profileAttributes = $profile->getAttributes('normal');
                    foreach ($fromParentAttrs as $fromParentAttr) {
                        $magentoAttr = isset($profileAttributes[$fromParentAttr]) ? $profileAttributes[$fromParentAttr]['magento_attribute_code'] : '';
                        if (!empty($magentoAttr)) {
                            $configProdValue = $configurableProduct->getData($magentoAttr);
                            $product->setData($magentoAttr, $configProdValue);
                        }
                    }
                    $this->ids[] = $product->getId();
                    $attributes = $this->prepareAttributes(
                        $product,
                        $profile
                    );

                    $attrKey = 0;
                    /*$requiredArray = array('internal-sku', 'title', 'product-reference-type',
                                'product-reference-value', 'product-description', 'brand');*/
                    $requiredArray = array('sku', 'product-id', 'product-id-type',
                        'description', 'internal-description', 'price', 'price-additional-info', 'quantity',
                        'min-quantity-alert', 'state', 'available-start-date', 'available-end-date', 'logistic-class',
                        'discount-price', 'discount-start-date', 'discount-end-date', 'leadtime-to-ship', 'update-delete',
                        'club-rueducommerce-eligible', 'tax-au', 'best-before-date', 'expiry-date',
                        'sales-price-1', 'sales-price-2', 'sales-price-3', 'sales-start-date-1', 'sales-start-date-2', 'sales-start-date-3',
                        'sales-end-date-1', 'sales-end-date-2', 'sales-end-date-3', 'sales-flash-price', 'sales-flash-start-date', 'sales-flash-end-date',
                        'copyright-tax', 'corepile-tax', 'dea-tax', 'deb-tax', 'deee-tax', 'ecoemballage-tax', 'ecofolio-tax', 'ecomobilier-tax',
                        'merchant-warranty', 'grade');

                    foreach ($attributes as $attKey => $attrValue) {

                        if (!in_array($attKey, $requiredArray)) {
                            if(in_array($attKey, ['brand'])) {
                                $attrValue = strtolower($attrValue);
                            }
                            $product_array[$attrKey] = array(
                                'attribute' => array(
                                    '_attribute' => array(),
                                    '_value' => array(
                                        'code' => (string)$attKey,
                                        'value' => (string)$attrValue
                                    )
                                )
                            );
                            $attrKey++;
                        }
                    }
                    if ($id['upload_as_simple'] == 'false') {
                        $product_array[$attrKey] = array(
                            'attribute' => array(
                                '_attribute' => array(),
                                '_value' => array(
                                    'code' => 'variant-id',
                                    'value' => $id['variantid']
                                )
                            )
                        );
                        $attrKey++;
                    }
                    $product_array[$attrKey] = array(
                        'attribute' => array(
                            '_attribute' => array(),
                            '_value' => array(
                                'code' => 'category',
                                'value' => $category
                            )
                        )
                    );
                    $attrKey++;
                    /*$product_array[$attrKey] = array(
                        'attribute' => array(
                            '_attribute' => array(),
                                '_value' => array(
                                    'code' => 'product-reference-type',
                                    'value' => strtolower($this->getProductReference('type',$product))
                                )
                            )
                        );
                    $attrKey++;

                    $product_array[$attrKey] = array(
                        'attribute' => array(
                            '_attribute' => array(),
                                '_value' => array(
                                    'code' => 'product-reference-value',
                                    'value' => $this->getProductReference('value',$product)
                                )
                            )
                        );
                    $attrKey++;*/
                    $additionalImages = $this->prepareImages($product, $attrKey, $configurableProduct);

                    if (count($additionalImages) == 0) {
                        $additionalImages = $this->prepareImages($configurableProduct, $attrKey);

                    }

                    $pdata = array_merge($product_array, $additionalImages);
                    $this->data[$this->key]['product'] = array(
                        '_attribute' => array(),
                        '_value' => $pdata

                    );
                    $this->data = array_values($this->data);
                    $this->key++;
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Create Configurable Product', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    /**
     * Save Response to db
     * @param array $response
     * @return boolean
     */
    public function saveResponse($responses = [])
    {
        //remove index if already set.
        $this->registry->unregister('rueducommerce_product_errors');

        if (is_array($responses)) {

            try {
                foreach ($responses as $response) {
                    $this->registry->unregister('rueducommerce_product_errors');
                    $this->registry->register('rueducommerce_product_errors', $response);
                    $feedModel = $this->feeds->create();
                    $feedModel->addData([
                        'feed_id' => $response['feed_id'],
                        'type' => $response['feed_type'],
                        'feed_response' => $this->json->jsonEncode(
                            ['Body' => $response, 'Errors' => $response]
                        ),
                        'status' => (string)$response['feed_status'],
                        'feed_file' => $response['feed_file'],
                        'response_file' => $response['feed_file'],
                        'feed_created_date' => $this->dateTime->date("Y-m-d"),
                        'feed_executed_date' => $this->dateTime->date("Y-m-d"),
                        'product_ids' => $this->json->jsonEncode($this->ids)
                    ]);
                    $feedModel->save();

                    foreach ($this->ids as $id) {
                        $product = $this->product->create()->load($id);
                        if (isset($product)) {
                            $product->setRueducommerceFeedErrors($this->json->jsonEncode($response));
                            $product->getResource()
                                ->saveAttribute($product, 'rueducommerce_feed_errors');
                        }
                    }
                }

                return true;
            } catch (\Exception $e) {
                $this->logger->error('Save Product/Offer Response', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            }
        }
        return false;
    }

    public function deleteProducts($ids = [])
    {
        $response = false;
        if (!empty($ids)) {
            $this->data = [
                0 => [
                    'Product' => [
                        '_attribute' => [],
                        '_value' => [
                            0 => [
                                'Skus' => [
                                    '_attribute' => [],
                                    '_value' => [
                                        0 => [
                                            'Sku' => [
                                                '_attribute' => [],
                                                '_value' => [
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]

                ]
            ];

            foreach ($ids as $id) {
                $product = $this->product->create()->load($id);
                // configurable Product
                if ($product->getTypeId() == 'configurable' &&
                    $product->getVisibility() != 1
                ) {
                    $configurableProduct = $product;
                    $productType = $configurableProduct->getTypeInstance();
                    $products = $productType->getUsedProducts($configurableProduct);
                    foreach ($products as $product) {
                        $this->data[0]['Product']['_value'][0]['Skus']['_value'][0]['Sku']['_value'][]['SellerSku'] = (string)$product->getSku();
                    }
                } elseif ($product->getTypeId() == 'simple' &&
                    $product->getVisibility() != 1
                ) {
                    $this->data[0]['Product']['_value'][0]['Skus']['_value'][0]['Sku']['_value'][]['SellerSku'] = (string)($product->getSku());
                }
            }
            $response = $this->rueducommerce->create(['config' => $this->config->getApiConfig()])->deleteProduct($this->data);
            if ($response and
                $response->getStatus() == \RueDuCommerceSdk\Api\Response::REQUEST_STATUS_SUCCESS and
                empty($response->getError())) {
                $this->updateStatus($this->ids, \Ced\RueDuCommerce\Model\Source\Product\Status::NOT_UPLOADED);
            }

            $response = $this->saveResponse($response);
            return $response;
        }
        return $response;
    }

    /**
     * @return bool
     */
    public function updatePriceInventory($ids = [], $withProducts = false, $makeInactive = false)
    {
        try {
            $fromParentAttrs = $this->config->getFromParentAttributes();
            $index = 0;
            $response = false;
            if (!empty($ids)) {
                $this->ids = [];
                $this->data = [
                    0 => [
                        'offer' => []
                    ]
                ];
                $threshold_status = $this->config->getThresholdStatus();
                $threshold_limit = $this->config->getThresholdLimit();
                $threshold_min = $this->config->getThresholdLimitMin();
                $threshold_max = $this->config->getThresholdLimitMax();
                foreach ($ids as $origIndex => $id) {
                    $product = $this->product->create()->load($id);
                    $profile = $this->profileHelper->getProfile($product->getId());

                    // configurable Product
                    if ($product->getTypeId() == 'configurable' &&
                        $product->getVisibility() != 1
                    ) {
                        $this->ids[] = $product->getId();
                        $configurableProduct = $product;
                        $productType = $configurableProduct->getTypeInstance();
                        $products = $productType->getUsedProducts($configurableProduct);
                        $profileAttributes = $profile->getAttributes('normal');
                        $cindex = $index;
                        foreach ($products as $product) {
                            $product = $this->product->create()->load($product->getId());
                            foreach ($fromParentAttrs as $fromParentAttr) {
                                $magentoAttr = isset($profileAttributes[$fromParentAttr]) ? $profileAttributes[$fromParentAttr]['magento_attribute_code'] : '';
                                if (!empty($magentoAttr)) {
                                    $configProdValue = $configurableProduct->getData($magentoAttr);
                                    $product->setData($magentoAttr, $configProdValue);
                                }
                            }
                            $this->ids[] = $product->getId();
                            $attributes = $this->prepareAttributes(
                                $product,
                                $profile
                            );
                            $this->data[$cindex]['offer']['sku'] = (string)($product->getSku());
                            $this->data[$cindex]['offer']['product-id'] = (string)($product->getBarcode());
                            $this->data[$cindex]['offer']['product-id-type'] = 'EAN';

                            if ($threshold_status) {
                                $quantity = $this->stockState->getStockQty(
                                    $product->getId(),
                                    $product->getStore()->getWebsiteId()
                                );
                                if ($quantity <= $threshold_limit) {
                                    $this->data[$cindex]['offer']['quantity'] = (string)$threshold_min;
                                } else {
                                    $this->data[$cindex]['offer']['quantity'] = (string)$threshold_max;
                                }
                            } else {
                                $this->data[$cindex]['offer']['quantity'] = (string)$this->stockState->getStockQty(
                                    $product->getId(),
                                    $product->getStore()->getWebsiteId()
                                );
                            }
                            if ($makeInactive === true) {
                                $this->data[$cindex]['offer']['quantity'] = '0';
                            }

                            $stateArray = array(
                                'New' => 11,
                                'Refurbished' => 10,
                                'Outlet - Refurbished' => 7,
                                'Outlet - New' => 8,
                            );

                            $requiredArray = array('sku', 'product-id', 'product-id-type',
                                'description', 'internal-description', 'price', 'price-additional-info', 'quantity',
                                'min-quantity-alert', 'state', 'available-start-date', 'available-end-date', 'logistic-class',
                                'discount-price', 'discount-start-date', 'discount-end-date', 'leadtime-to-ship', 'update-delete',
                                'club-rueducommerce-eligible', 'tax-au', 'best-before-date', 'expiry-date',
                                'sales-price-1', 'sales-price-2', 'sales-price-3', 'sales-start-date-1', 'sales-start-date-2', 'sales-start-date-3',
                                'sales-end-date-1', 'sales-end-date-2', 'sales-end-date-3', 'sales-flash-price', 'sales-flash-start-date', 'sales-flash-end-date',
                                'copyright-tax', 'corepile-tax', 'dea-tax', 'deb-tax', 'deee-tax', 'ecoemballage-tax', 'ecofolio-tax', 'ecomobilier-tax',
                                'merchant-warranty', 'grade');

                            foreach ($attributes as $attKey => $attrValue) {
                                if (in_array($attKey, $requiredArray)) {
                                    if ($attKey == "price") {
                                        $this->data[$cindex]['offer'][$attKey] = (string)($this->getPrice($product, $attrValue)['special_price']);
                                        continue;
                                    }
                                    if ($attKey == "state") {
                                        $this->data[$cindex]['offer'][$attKey] = (string)isset($stateArray[$attrValue]) ? (string)$stateArray[$attrValue] : (string)$attrValue;
                                        continue;
                                    }
                                    if (in_array($attKey, array('discount-start-date', 'discount-end-date', 'available-start-date', 'available-end-date'))) {
                                        if (in_array($attKey, array('discount-start-date', 'discount-end-date'))) {
                                            if ($attrValue != NULL && isset($attributes['discount-price'])) {
                                                $attrValue = date('Y-m-d', strtotime($attrValue));
                                                $this->data[$cindex]['offer'][$attKey] = (string)$attrValue;
                                            }
                                            continue;
                                        }
                                        if ($attrValue != NULL) {
                                            $attrValue = date('Y-m-d', strtotime($attrValue));
                                            $this->data[$cindex]['offer'][$attKey] = (string)$attrValue;
                                        }
                                        continue;
                                    }
                                    if (in_array($attKey, array('best-before-date', 'expiry-date', 'sales-start-date-1',
                                        'sales-start-date-2', 'sales-start-date-3', 'sales-end-date-1', 'sales-end-date-2',
                                        'sales-end-date-3', 'sales-flash-start-date', 'sales-flash-end-date'))) {
                                        if ($attrValue != NULL) {
                                            $attrValue = date('Y-m-d', strtotime($attrValue));
                                            $this->data[$cindex]['offer']['offer-additional-fields'][] = array(
                                                'offer-additional-field' => array(
                                                    '_attribute' => array(),
                                                    '_value' => array(
                                                        'code' => $attKey,
                                                        'value' => (string)$attrValue
                                                    )
                                                )
                                            );
                                        }
                                        continue;
                                    }
                                    if (in_array($attKey, array('club-rueducommerce-eligible', 'tax-au', 'sales-price-1',
                                        'sales-price-2', 'sales-price-3', 'sales-flash-price', 'copyright-tax', 'corepile-tax', 'dea-tax', 'deb-tax', 'deee-tax', 'ecoemballage-tax', 'ecofolio-tax', 'ecomobilier-tax',
                                        'merchant-warranty', 'grade'))) {
                                        if ($attrValue != NULL) {
                                            $this->data[$cindex]['offer']['offer-additional-fields'][] = array(
                                                'offer-additional-field' => array(
                                                    '_attribute' => array(),
                                                    '_value' => array(
                                                        'code' => $attKey,
                                                        'value' => (string)$attrValue
                                                    )
                                                )
                                            );
                                        }
                                        continue;
                                    }
                                    if ($attrValue != NULL) {
                                        $this->data[$cindex]['offer'][$attKey] = (string)$attrValue;
                                    }
                                }
                            }
                            if (isset($this->data[$cindex]['offer']['offer-additional-fields'])) {
                                $this->data[$cindex]['offer']['offer-additional-fields'] = array(
                                    '_attribute' => array(),
                                    '_value' => $this->data[$cindex]['offer']['offer-additional-fields']

                                );
                            }
                            if (!isset($this->data[$cindex]['offer']['discount-end-date']) && isset($this->data[$cindex]['offer']['discount-price'])) {
                                $this->data[$cindex]['offer']['discount-end-date'] = '';
                            }
                            if (!isset($this->data[$cindex]['offer']['discount-start-date']) && isset($this->data[$cindex]['offer']['discount-price'])) {
                                $this->data[$cindex]['offer']['discount-start-date'] = '';
                            }
                            $cindex++;
                        }
                        $index = $cindex;
                    } elseif ($product->getTypeId() == 'simple' &&
                        $product->getVisibility() != 1
                    ) {
                        $this->ids[] = $product->getId();
                        $attributes = $this->prepareAttributes(
                            $product,
                            $profile
                        );
                        $this->data[$index]['offer']['sku'] = (string)($product->getSku());
                        $this->data[$index]['offer']['product-id'] = (string)($product->getBarcode());
                        $this->data[$index]['offer']['product-id-type'] = 'EAN';

                        if ($threshold_status) {
                            $quantity = $this->stockState->getStockQty(
                                $product->getId(),
                                $product->getStore()->getWebsiteId()
                            );
                            if ($quantity <= $threshold_limit) {
                                $this->data[$index]['offer']['quantity'] = (string)$threshold_min;
                            } else {
                                $this->data[$index]['offer']['quantity'] = (string)$threshold_max;
                            }
                        } else {
                            $this->data[$index]['offer']['quantity'] = (string)$this->stockState->getStockQty(
                                $product->getId(),
                                $product->getStore()->getWebsiteId()
                            );
                        }
                        if ($makeInactive === true) {
                            $this->data[$index]['offer']['quantity'] = '0';
                        }

                        $stateArray = array(
                            'New' => 11,
                            'Refurbished' => 10,
                            'Outlet - Refurbished' => 7,
                            'Outlet - New' => 8,
                        );

                        $requiredArray = array('sku', 'product-id', 'product-id-type',
                            'description', 'internal-description', 'price', 'price-additional-info', 'quantity',
                            'min-quantity-alert', 'state', 'available-start-date', 'available-end-date', 'logistic-class',
                            'discount-price', 'discount-start-date', 'discount-end-date', 'leadtime-to-ship', 'update-delete',
                            'club-rueducommerce-eligible', 'tax-au', 'best-before-date', 'expiry-date',
                            'sales-price-1', 'sales-price-2', 'sales-price-3', 'sales-start-date-1', 'sales-start-date-2', 'sales-start-date-3',
                            'sales-end-date-1', 'sales-end-date-2', 'sales-end-date-3', 'sales-flash-price', 'sales-flash-start-date', 'sales-flash-end-date',
                            'copyright-tax', 'corepile-tax', 'dea-tax', 'deb-tax', 'deee-tax', 'ecoemballage-tax', 'ecofolio-tax', 'ecomobilier-tax',
                            'merchant-warranty', 'grade');

                        foreach ($attributes as $attKey => $attrValue) {
                            if (in_array($attKey, $requiredArray)) {
                                if ($attKey == "price") {
                                    $this->data[$index]['offer'][$attKey] = (string)($this->getPrice($product, $attrValue)['special_price']);
                                    continue;
                                }
                                if ($attKey == "state") {
                                    $this->data[$index]['offer'][$attKey] = (string)isset($stateArray[$attrValue]) ? (string)$stateArray[$attrValue] : (string)$attrValue;
                                    continue;
                                }
                                if (in_array($attKey, array('discount-start-date', 'discount-end-date', 'available-start-date', 'available-end-date'))) {
                                    if (in_array($attKey, array('discount-start-date', 'discount-end-date'))) {
                                        if ($attrValue != NULL && isset($attributes['discount-price'])) {
                                            $attrValue = date('Y-m-d', strtotime($attrValue));
                                            $this->data[$index]['offer'][$attKey] = (string)$attrValue;
                                        }
                                        continue;
                                    }
                                    if ($attrValue != NULL) {
                                        $attrValue = date('Y-m-d', strtotime($attrValue));
                                        $this->data[$index]['offer'][$attKey] = (string)$attrValue;
                                    }
                                    continue;
                                }
                                if (in_array($attKey, array('best-before-date', 'expiry-date'))) {
                                    if ($attrValue != NULL) {
                                        $attrValue = date('Y-m-d', strtotime($attrValue));
                                        $this->data[$index]['offer']['offer-additional-fields'][] = array(
                                            'offer-additional-field' => array(
                                                '_attribute' => array(),
                                                '_value' => array(
                                                    'code' => $attKey,
                                                    'value' => (string)$attrValue
                                                )
                                            )
                                        );
                                    }
                                    continue;
                                }
                                if (in_array($attKey, array('club-rueducommerce-eligible', 'tax-au'))) {
                                    if ($attrValue != NULL) {
                                        $this->data[$index]['offer']['offer-additional-fields'][] = array(
                                            'offer-additional-field' => array(
                                                '_attribute' => array(),
                                                '_value' => array(
                                                    'code' => $attKey,
                                                    'value' => (string)$attrValue
                                                )
                                            )
                                        );
                                    }
                                    continue;
                                }
                                if ($attrValue != NULL) {
                                    $this->data[$index]['offer'][$attKey] = (string)$attrValue;
                                }
                            }
                        }
                        if (isset($this->data[$index]['offer']['offer-additional-fields'])) {
                            $this->data[$index]['offer']['offer-additional-fields'] = array(
                                '_attribute' => array(),
                                '_value' => $this->data[$index]['offer']['offer-additional-fields']

                            );
                        }
                        if (!isset($this->data[$index]['offer']['discount-end-date']) && isset($this->data[$index]['offer']['discount-price'])) {
                            $this->data[$index]['offer']['discount-end-date'] = '';
                        }
                        if (!isset($this->data[$index]['offer']['discount-start-date']) && isset($this->data[$index]['offer']['discount-price'])) {
                            $this->data[$index]['offer']['discount-start-date'] = '';
                        }
                        $index++;
                    }
                }
                if ($withProducts) {
                    $this->offerData = $this->data;
                    $this->data = [];
                    $this->prepareProductData($ids);
                    $response = $this->rueducommerce->create(['config' => $this->config->getApiConfig()])
                        ->createOfferWithProduct($this->offerData, $this->data);
                    $response = $this->saveResponse($response);
                    return $response;
                }
                $response = $this->rueducommerce->create(['config' => $this->config->getApiConfig()])
                    ->createOffer($this->data);
                $response = $this->saveResponse($response);
                return $response;
            }
            return $response;
        } catch (\Exception $e) {
            $this->logger->error('Offer Update', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * Update Product Status
     * @param string $status
     * @return bool
     */
    public function updateStatus($ids = [], $status = \Ced\RueDuCommerce\Model\Source\Product\Status::UPLOADED)
    {
        if (!empty($ids) and is_array($ids) and
                in_array($status, \Ced\RueDuCommerce\Model\Source\Product\Status::STATUS)
        ) {
            foreach ($ids as $index => $product) {
            //$products = $this->product->create()->getCollection()
              //  ->addAttributeToSelect(['rueducommerce_product_status'])
                //->addAttributeToFilter('entity_id', ['in' => $this->ids]);
            //foreach ($products as $product) {
                $product = $this->product->create()->load($product);
                $product->setData('rueducommerce_product_status', $status);
                $product->getResource()->saveAttribute($product, 'rueducommerce_product_status');
            //}
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

    public function getProductReference($type='',$product='')
    {
        if($type=='type'){
            $type = $this->config->getReferenceType();
            return $type;
        }
        if($type=='value'){
            $type = $this->config->getReferenceValue();
            if($type) {
                $attr = $product->getResource()->getAttribute($type);
                $refType = $this->config->getReferenceType();
                if ($attr) {
                    if ($attr->getSourceModel($attr) || $attr->getData('frontend_input') == 'select') {
                            $valueId =  $product->getData($type);
                            $value = $attr->getSource()->getOptionText($valueId);
                            $result = $this->validateProductId($value, $refType);
                            if($result)
                            return $value;
                            return $result;
                        } else {
                           $value =  $product->getData($type);
                           $result = $this->validateProductId($value, $refType);
                           if($result)
                            return $value;
                           return $result;
                        }
                    } else {
                           $value =  $product->getData($type);
                           $result = $this->validateProductId($value, $refType);
                           if($result)
                            return $value;
                           return $result;
                    }
            
            }
            
        }
        return false;
    }
    /**
     * Function Product Id Validate
     */
    /*public function validateProductId($productID, $barcodeType)
    {
        $barcodeType = strtolower($barcodeType);
        $productID = trim($productID);
        switch ($barcodeType) {
            case 'ean':
                $isValid = \Ced\RueDuCommerce\Helper\BarcodeValidator::IsValidEAN14($productID);

                if($isValid){
                    return true;
                } else {
                    $isValid = \Ced\RueDuCommerce\Helper\BarcodeValidator::IsValidEAN13($productID);
                    if($isValid){
                        return true;
                    } else {
                        $isValid = \Ced\RueDuCommerce\Helper\BarcodeValidator::IsValidEAN8($productID);
                        if($isValid){
                            return true;
                        } else {
                            return false;
                        }
                    }
                }
                break;
            case 'isbn':
                $isValid = \Ced\RueDuCommerce\Helper\BarcodeValidator::IsValidISBN($productID);
                if($isValid){
                    return true;
                } else {
                    return false;
                }
                break;
            case 'upc':
                if(strlen($productID) % 2 == 0){
                    $productID = 0 . $productID;
                }
                $isValid = \Ced\RueDuCommerce\Helper\BarcodeValidator::IsValidUPCA($productID);
                if($isValid){
                    return true;
                } else {
                    $isValid = \Ced\RueDuCommerce\Helper\BarcodeValidator::IsValidUPCE($productID);
                    if($isValid){
                        return true;
                    } else {
                        return false;
                    }
                }
                break;
            case 'mpn':
                return true;
                break;            
            
            default:
                return false;
                break;
        }
    }*/

    public function validateProductId($productID, $barcodeType) {
        if(!in_array($barcodeType, array('upc', 'ean', 'isbn', 'mpn'))) {
            return false;
        }
        if($barcodeType == 'mpn') {
            return true;
        }
        if (preg_match('/[^0-9]/', $productID))
        {
            // is not numeric
            return false;
        }
        // pad with zeros to lengthen to 14 digits
        switch (strlen($productID))
        {
            case 8:
                $productID = "000000".$productID;
                break;
            case 12:
                $productID = "00".$productID;
                break;
            case 13:
                $productID = "0".$productID;
                break;
            case 14:
                break;
            default:
                // wrong number of digits
                return false;
        }
        // calculate check digit
        $a = array();
        $a[0] = (int)($productID[0]) * 3;
        $a[1] = (int)($productID[1]);
        $a[2] = (int)($productID[2]) * 3;
        $a[3] = (int)($productID[3]);
        $a[4] = (int)($productID[4]) * 3;
        $a[5] = (int)($productID[5]);
        $a[6] = (int)($productID[6]) * 3;
        $a[7] = (int)($productID[7]);
        $a[8] = (int)($productID[8]) * 3;
        $a[9] = (int)($productID[9]);
        $a[10] = (int)($productID[10]) * 3;
        $a[11] = (int)($productID[11]);
        $a[12] = (int)($productID[12]) * 3;
        $sum = $a[0] + $a[1] + $a[2] + $a[3] + $a[4] + $a[5] + $a[6] + $a[7] + $a[8] + $a[9] + $a[10] + $a[11] + $a[12];
        $check = (10 - ($sum % 10)) % 10;
        // evaluate check digit
        $last = (int)($productID[13]);
        return $check == $last;
    }


    /**
     * Get Feeds, Get Single Feed, Get Single Feed with Error Details
     * @param null $feedId
     * @param string $subUrl
     * @return array|boolean
     * @link  https://www.rueducommercecommerceservices.com/question/current-xml-puts-and-gets/
     */
    public function getFeeds($feedId = null, $feedData = null)
    {
        //$response = null;
        try {
            if($feedData != null) {
                if (is_array($feedData)) {
                    reset($feedData); // make sure array pointer is at first element
                    $firstKey = key($feedData);
                }
                if (isset($feedData[$firstKey]['has_error_report']) && ($feedData[$firstKey]['has_error_report'] == 'true')) {
                    if($firstKey == 'product_import_tracking') {
                        $feedData = $this->rueducommerce->create(['config' => $this->config->getApiConfig()])->getFeeds($feedId ,\RueDuCommerceSdk\Core\Request::POST_ITEMS_SUB_URL.'/'.$feedId.'/'.'error_report');
                    } else {
                        $feedData = $this->rueducommerce->create(['config' => $this->config->getApiConfig()])->getFeeds($feedId ,\RueDuCommerceSdk\Core\Request::POST_OFFER_IMPORT.'/'.$feedId.'/'.'error_report');
                    }
                } elseif (isset($feedData[$firstKey]['has_new_product_report']) && ($feedData[$firstKey]['has_new_product_report'] == 'true')) {
                    $feedData = $this->rueducommerce->create(['config' => $this->config->getApiConfig()])->getFeeds($feedId ,\RueDuCommerceSdk\Core\Request::POST_ITEMS_SUB_URL.'/'.$feedId.'/'.'new_product_report');
                } elseif (isset($feedData[$firstKey]['has_transformation_error_report']) && ($feedData[$firstKey]['has_transformation_error_report'] == 'true')) {
                    $feedData = $this->rueducommerce->create(['config' => $this->config->getApiConfig()])->getFeeds($feedId ,\RueDuCommerceSdk\Core\Request::POST_ITEMS_SUB_URL.'/'.$feedId.'/'.'transformation_error_report');
                } elseif (isset($feedData[$firstKey]['has_transformed_file']) && ($feedData[$firstKey]['has_transformed_file'] == 'true')) {
                    $feedData = $this->rueducommerce->create(['config' => $this->config->getApiConfig()])->getFeeds($feedId ,\RueDuCommerceSdk\Core\Request::POST_ITEMS_SUB_URL.'/'.$feedId.'/'.'transformed_file');
                }
                return $this->json->jsonEncode($feedData);
            }
        } catch (\Exception $e) {
            $this->logger->error('Get Feeds', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }


    /**
     * Sync Feed
     */
    public function syncFeeds($feed = null)
    {
        try {
            if ($feed and $feed->getId()) {
                if ($feed->getType() == 'inventory-update')
                    $feed_data = $this->rueducommerce->create(['config' => $this->config->getApiConfig()])->getFeeds($feed->getFeedId(), \RueDuCommerceSdk\Core\Request::POST_OFFER_IMPORT . '/' . $feed->getFeedId());
                else
                    $feed_data = $this->rueducommerce->create(['config' => $this->config->getApiConfig()])->getFeeds($feed->getFeedId(), \RueDuCommerceSdk\Core\Request::POST_ITEMS_SUB_URL . '/' . $feed->getFeedId());

                $feedError = $this->getFeeds($feed->getFeedId(), $feed_data);
                $productIds = json_decode($feed->getProductIds());
                if (is_array($productIds)) {
                    $attrData = array('rueducommerce_feed_errors' => $feedError);
                    $storeId = 0;
                    $this->_prodAction->updateAttributes($productIds, $attrData, $storeId);
                }
                if (isset($feed_data['product_import_tracking'])) {
                    $feed_data = $feed_data['product_import_tracking'];
                    if (isset($feed_data['import_status']))
                        $feed_data['status'] = $feed_data['import_status'];
                    $feed->setData('transform_lines_in_error', $feed_data['transform_lines_in_error']);
                    $feed->setData('transform_lines_in_success', $feed_data['transform_lines_in_success']);
                    $feed->setData('transform_lines_read', $feed_data['transform_lines_read']);
                    $feed->setData('transform_lines_with_warning', $feed_data['transform_lines_with_warning']);
                    $feed->setData('status', $feed_data['import_status']);
                    $feed->setData('feed_response', $feedError);
                    $feed->save();
                } else if (isset($feed_data['import'])) {
                    $errorIds = $successIds = [];
                    $feedError = $this->json->jsonDecode($feedError);
                    $skus = isset($feedError['import']['offers']['offer']) ? array_column($feedError['import']['offers']['offer'], 'sku') : array();
                    if (is_array($productIds)) {
                        if (empty($skus)) {
                            $successIds = $productIds;
                        }
                        foreach ($productIds as $productId) {
                            $isChild = false;
                            $prod = $this->prodCollection->create()->load($productId);
                            $productParents = $this->configProduct->create()
                                ->getParentIdsByChild($productId);
                            if (!empty($productParents)) {
                                $isChild = true;
                            }
                            $profile = $this->profileHelper->getProfile($productId);
                            $requiredAttributes = $profile->getRequiredAttributes();
                            if ($profile->getId()) {
                                $mappedSku = isset($requiredAttributes['internal-sku']['magento_attribute_code']) ? $requiredAttributes['internal-sku']['magento_attribute_code'] : '';
                                if (array_search($prod->getData($mappedSku), $skus) !== false) {
                                    $errorIds[] = $productId;
                                    if ($isChild && isset($productParents[0]) && !in_array($productParents[0], $errorIds)) {
                                        $errorIds[] = $productParents[0];
                                    }
                                } else if ($isChild) {
                                    $successIds[] = $productId;
                                }
                            }
                        }

                        $storeId = 0;
                        if (isset($successIds) && is_array($successIds) && count($successIds) > 0) {
                            //$successData = array( 'rueducommerce_product_status' => 'LIVE' );
                            //$this->_prodAction->updateAttributes($successIds, $successData, $storeId);
                        }
                        if (isset($errorIds) && is_array($errorIds) && count($errorIds) > 0) {
                            $errorData = array('rueducommerce_product_status' => 'INVALID');
                            $this->_prodAction->updateAttributes($errorIds, $errorData, $storeId);
                        }
                    }
                    $feedError = $this->json->jsonEncode($feedError);
                    $feed_data = $feed_data['import'];
                    if (isset($feed_data['import_status']))
                        $feed_data['status'] = $feed_data['status'];
                    $feed->setData('transform_lines_in_error', isset($feed_data['lines_in_error']) ? $feed_data['lines_in_error'] : '');
                    $feed->setData('transform_lines_in_success', isset($feed_data['lines_in_success']) ? $feed_data['lines_in_success'] : '');
                    $feed->setData('transform_lines_read', isset($feed_data['lines_read']) ? $feed_data['lines_read'] : '');
                    $feed->setData('transform_lines_with_warning', isset($feed_data['lines_in_pending']) ? $feed_data['lines_in_pending'] : '');
                    $feed->setData('status', $feed_data['status']);
                    $feed->setData('feed_response', $feedError);
                    $feed->save();
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Sync Feeds', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    /**
     * Create/Update Product on RueDuCommerce
     * @param [] $ids
     * @return bool
     */
    public function prepareProductData($ids = [])
    {
        try {
            $response = false;
            $ids = $this->validateAllProducts($ids);
            if (!empty($ids['simple']) or !empty($ids['configurable'])) {
                $this->ids = [];
                $this->key = 0;
                $this->data = [];
                $this->prepareSimpleProducts($ids['simple']);

                $this->prepareConfigurableProducts($ids['configurable']);
            }
            return $response;
        } catch (\Exception $e) {
            $this->logger->error('Prepare Offer Product Data', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    public function getProductIdsFromOfferAPI()
    {
        try {
            $excludedProdIds = $this->product->create()->getCollection()
                ->addAttributeToFilter('rueducommerce_exclude_from_sync', array('eq' => '1'))
                ->getColumnValues('entity_id');
            if(count($excludedProdIds) <= 0) {
                $excludedProdIds = '';
            }
            $invChunkSize = 100;
            $offsetNumber = $this->cache->load("ced_rueducommerce_offset_number");
            $offsetNumber = ($offsetNumber == '') ? 0 : $offsetNumber;
            $offers = $this->rueducommerce->create(['config' => $this->config->getApiConfig()])->getOffers($invChunkSize, $offsetNumber);
            $offerWithStatus = array_column($offers, 'active', 'shop_sku');
            $activeProducts = array_keys($offerWithStatus, "true");
            $inActiveProducts = array_keys($offerWithStatus, "false");
            $storeId = 0;
            $activeIds = $this->product->create()->getCollection()
                ->addAttributeToFilter('sku', array('in' => $activeProducts))
                ->getColumnValues('entity_id');
            $liveData = array( 'rueducommerce_product_status' => 'LIVE' );
            $this->_prodAction->updateAttributes($activeIds, $liveData, $storeId);
            $inActiveIds = $this->product->create()->getCollection()
                ->addAttributeToFilter('sku', array('in' => $inActiveProducts))
                ->getColumnValues('entity_id');
            $inactiveData = array( 'rueducommerce_product_status' => 'INACTIVE' );
            $this->_prodAction->updateAttributes($inActiveIds, $inactiveData, $storeId);
            $offerskus = array_column($offers, 'shop_sku');
            $this->updateParentProductStatus($offerskus);
            if (count($offerskus) > 0) {
                $offsetNumber = $offsetNumber + $invChunkSize;
            } else {
                $offsetNumber = 0;
            }
            $this->cache->save((string)($offsetNumber), "ced_rueducommerce_offset_number", array('block_html'), 99999);
            $prodCollection = $this->product->create()->getCollection()
                ->addAttributeToFilter('sku', array('in' => $offerskus))
                ->addAttributeToFilter('entity_id', array('nin' => $excludedProdIds));
            $prodIds = $prodCollection->getColumnValues('entity_id');
        } catch (\Exception $e) {
            $this->logger->addError('Product Ids for error has error', array('path' => __METHOD__, 'exception' => $e->getMessage()));
            return false;
        }
        return $prodIds;
    }

    public function updateParentProductStatus($prodSkus = array())
    {
        try {
            $parentIds = $this->session->getData('parent_ids');
            if(!empty($parentIds)) {
                $parentIds = $this->json->jsonDecode($parentIds);
            } else {
                $parentIds = [];
            }
            $prodCollection = $this->product->create()->getCollection()
                ->addAttributeToFilter('sku', array('in' => $prodSkus));
            foreach ($prodCollection as $prod) {
                $productParents = $this->objectManager
                    ->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
                    ->getParentIdsByChild($prod->getId());
                if (!empty($productParents)) {
                    $profile = $this->profileHelper->getProfile($productParents[0]);
                    if (!empty($profile)) {
                        $configurableProduct = $this->product->create()->load($productParents[0])
                            ->setStoreId($this->selectedStore);
                        if(!in_array($configurableProduct->getId(), $parentIds)) {
                            if ($configurableProduct->getTypeId() == 'configurable' &&
                                $configurableProduct->getVisibility() != 1
                            ) {
                                $parentIds[] = $configurableProduct->getId();
                                $this->session->setData('parent_ids', $this->json->jsonEncode($parentIds));
                                $productType = $configurableProduct->getTypeInstance();
                                $products = $productType->getUsedProducts($configurableProduct);
                                $rueducommerceStatus = [];
                                foreach ($products as $product) {
                                    $product = $this->product->create()->load($product->getId())
                                        ->setStoreId($this->selectedStore);
                                    $rueducommerceStatus[] = $product->getRueducommerceProductStatus();
                                }
                                $childStatus = array_unique($rueducommerceStatus);
                                if (count($childStatus) > 1) {
                                    $configurableProduct->setRueducommerceProductStatus('PARTIAL');
                                    $configurableProduct->getResource()->saveAttribute($configurableProduct, 'rueducommerce_product_status');
                                } else {
                                    $configurableProduct->setRueducommerceProductStatus($childStatus[0]);
                                    $configurableProduct->getResource()->saveAttribute($configurableProduct, 'rueducommerce_product_status');
                                }
                            }
                        }
                    }
                }
            }
            $this->session->unsetData('parent_ids');
        } catch (\Exception $e) {
            $this->logger->addError('Product Ids for error has error', array('path' => __METHOD__, 'exception' => $e->getMessage()));
            return false;
        }
        return true;
    }

    public function getChildProductStatus($prodId = array())
    {
        try {
            $configurableProduct = $this->product->create()->load($prodId)
                ->setStoreId($this->selectedStore);
            if ($configurableProduct->getTypeId() == 'configurable') {
                $feedErrors = json_decode($configurableProduct->getRueducommerceFeedErrors(), true);
                $feedErrors = isset($feedErrors['import']['offers']['offer']) ? $feedErrors['import']['offers']['offer'] : array();
                if(count($feedErrors) > 0 && !isset($feedErrors[0])) {
                    $feedErrors[] = $feedErrors;
                }
                $feedErrorsWithSku = array_column($feedErrors, 'error-message', 'sku');
                $productType = $configurableProduct->getTypeInstance();
                $products = $productType->getUsedProducts($configurableProduct);
                $rueducommerceStatus = [];
                foreach ($products as $product) {
                    $product = $this->product->create()->load($product->getId())
                        ->setStoreId($this->selectedStore);
                    $rueducommerceStatus[$product->getSku()] = array('Status' => $product->getRueducommerceProductStatus(), 'Error' => isset($feedErrorsWithSku[$product->getSku()]) ? $feedErrorsWithSku[$product->getSku()] : 'Error Not Found');
                }
                return $rueducommerceStatus;
            } elseif ($configurableProduct->getTypeId() == 'simple') {
                $feedErrors = json_decode($configurableProduct->getRueducommerceFeedErrors(), true);
                $feedErrors = isset($feedErrors['import']['offers']['offer']) ? $feedErrors['import']['offers']['offer'] : array();
                if(count($feedErrors) > 0 && !isset($feedErrors[0])) {
                    $feedErrors[] = $feedErrors;
                }
                $feedErrorsWithSku = array_column($feedErrors, 'error-message', 'sku');
                $rueducommerceStatus[$configurableProduct->getSku()] = array('Status' => $configurableProduct->getRueducommerceProductStatus(), 'Error' => isset($feedErrorsWithSku[$configurableProduct->getSku()]) ? $feedErrorsWithSku[$configurableProduct->getSku()] : 'Error Not Found');
                return $rueducommerceStatus;
            }
        } catch (\Exception $e) {
            $this->logger->addError('Product Ids for error has error', array('path' => __METHOD__, 'exception' => $e->getMessage()));
            return array();
        }
        return array();
    }
}
