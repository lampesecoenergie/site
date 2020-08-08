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
 * @category  Ced
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Helper;

use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Xml\Parser;
use Magento\Setup\Module\Dependency\Parser\Config\Xml;
use Monolog\Handler\StreamHandler;

class Category extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ATTRIBUTE_SHORT_LABEL = 'ShortLabel';
    const ATTRIBUTE_SELLER_PRODUCT_ID = 'SellerProductId';
    const ATTRIBUTE_LONG_LABEL = 'LongLabel';
    const ATTRIBUTE_DESCRIPTION = 'Description';
    const ATTRIBUTE_BRAND_NAME = 'BrandName';
    const ATTRIBUTE_EAN = 'Ean';

    const ATTRIBUTE_ISBN = 'ISBN';
    const ATTRIBUTE_MFPN = 'ManufacturerPartNumber';
    const ATTRIBUTE_SELLER_PRODUCT_COLOR_NAME = 'SellerProductColorName';
    const ATTRIBUTE_SIZE = 'Size';
    const ATTRIBUTE_LENGTH = 'Length';
    const ATTRIBUTE_WIDTH = 'Width';
    const ATTRIBUTE_HEIGHT = 'Height';
    const ATTRIBUTE_WEIGHT = 'Weight';
    const ATTRIBUTE_COMMENT = 'Comment';
    const ATTRIBUTE_SALE_PRICE = 'Sale Price';

    const REQUIRED_ATTRIBUTES = [
        self::ATTRIBUTE_SHORT_LABEL,
        self::ATTRIBUTE_SELLER_PRODUCT_ID,
        self::ATTRIBUTE_LONG_LABEL,
        self::ATTRIBUTE_DESCRIPTION,
        self::ATTRIBUTE_BRAND_NAME,
        self::ATTRIBUTE_EAN,
    ];

    const OPTIONAL_ATTRIBUTES = [
        self::ATTRIBUTE_ISBN,
        self::ATTRIBUTE_MFPN,
        self::ATTRIBUTE_SELLER_PRODUCT_COLOR_NAME,
        self::ATTRIBUTE_SIZE,
        self::ATTRIBUTE_LENGTH,
        self::ATTRIBUTE_WIDTH,
        self::ATTRIBUTE_HEIGHT,
        self::ATTRIBUTE_WEIGHT,
        self::ATTRIBUTE_COMMENT,
        self::ATTRIBUTE_SALE_PRICE
    ];

    public $objectManager;
    public $parser;
    public $config;
    public $modelFilter;
    public $product;
    public $logger;
    public $categories = [];
    public $categoriesTree = [];
    public $apiFactory;
    public $categoriesFactory;
    public $modelname;

    public $defaultMapping = [
        'SellerProductId' => 'sku',
        'Vat' => 'vat',
        'ShortLabel' => 'name',
        'Description' => 'description',
        'LongLabel' => 'description',
        'PackageContent' => 'description',
        'BrandName' => 'brand_name',
        'Price' => 'price',

    ];

    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        \Magento\Framework\Xml\ParserFactory $parser,
        \Ced\Cdiscount\Helper\Config $config,
        \Ced\Cdiscount\Helper\Logger $logger,
        \Ced\Cdiscount\Helper\Product $product,
        \Ced\Cdiscount\Model\CategoriesFactory $categoriesFactory,
        \Sdk\ApiClient\CDSApiClientFactory $apiClientFactory,
        \Sdk\Product\ModelFilterFactory $modelFilter
    ) {
        $this->objectManager = $objectManager;
        $this->product = $product;
        $this->config = $config;
        $this->apiFactory = $apiClientFactory;
        $this->categoriesFactory = $categoriesFactory;
        $this->modelFilter = $modelFilter;
        $this->parser = $parser;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function getAttributes($type = 'model', $params = [])
    {
        try {
            /*$isEanOptional = $this->categoriesFactory->create()
                ->getEanOptional($params);*/
            $requiredAttributes = self::REQUIRED_ATTRIBUTES;
            $optionalAttributes = self::OPTIONAL_ATTRIBUTES;

            /*if ($isEanOptional == 'true') {
                unset($requiredAttributes[5]);
                $optionalAttributes[] = self::ATTRIBUTE_EAN;

            }*/
            $attributes = [];
            if ($type == 'required') {
                foreach ($requiredAttributes as $value) {
                    if (isset($this->defaultMapping[$value])) {
                        $attributes[] = ['label' => ($value), 'name' => $value, 'isMandatory' => 1,
                            'magento_attribute_code' => $this->defaultMapping[$value], 'model_attributes' => 0];
                    } else {
                        $attributes[] = ['label' => ($value), 'name' => $value, 'isMandatory' => 1,
                            'model_attributes' => 0];
                    }
                }
            } elseif ($type == 'optional') {
                foreach ($optionalAttributes as $optionalValue) {
                    $attributes[] = ['label' => ($optionalValue), 'name' => $optionalValue, 'isMandatory' => 0,
                        'model_attributes' => 0];
                }
            } elseif ($type == 'model') {
                $userName = $this->config->getUserName();
                $password = $this->config->getUserPassword();
                $models = $this->apiFactory->create(['username' => $userName, 'password' => $password]);
                $token = $models->init();
                if (!empty($token)) {
                    foreach ($requiredAttributes as $value) {
                        if (isset($this->defaultMapping[$value])) {
                            $attributes[] = ['label' => ($value), 'name' => $value, 'isMandatory' => 1,
                                'magento_attribute_code' => $this->defaultMapping[$value]];
                        } else {
                            $attributes[] = ['label' => ($value), 'name' => $value, 'isMandatory' => 1];
                        }
                    }
                    if (isset($params['category']) && !empty($params['category'])) {
                        $catCode = $params['category'];
                    } else {
                        $catCode = $params;
                    }
                    $codes = $this->modelFilter->create(['categoryCode' => "{$catCode}"]);
                    $allModels = $models->getProductPoint();
                    $allModels = ($allModels->getModelList($codes));
                    if (isset($allModels) and !empty($allModels)) {
                        foreach ($allModels->getModelList() as $allModel) {
                            $this->modelname = ($allModel->getName());
                            foreach ($allModel->getValueProperties() as $valueProperty) {
                                $opVals = [];
                                foreach ($valueProperty->getValues() as $id => $val) {
                                    if (isset($params['action'])
                                        && $params['action'] == 'attr') {
                                        $opVals["option-$id"] = $val;
                                    } else {
                                        $opVals[] = $val;
                                    }
                                }
                                if (is_array($allModel->getMandatoryModelProperties()) &&
                                    in_array($valueProperty->getKey(), $allModel->getMandatoryModelProperties())) {
                                    $attributes[] = ['label' => $valueProperty->getKey(),
                                        'name' => $valueProperty->getKey(),
                                        'options' => $opVals,
                                        'isMandatory' => 1,
                                        'model_attributes' => 1
                                    ];
                                }
                            }
                        }
                    }
                }
            }
            return $attributes;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['path' => __METHOD__]);
            return $attributes;
        }
    }

    public function getModelName()
    {
        return $this->modelname;
    }
    /**
     * @return array
     */

    public function getCategoriesTree()
    {
        $categoryModel = $this->categoriesFactory->create()
            ->getCollection()->getData();
        $this->categoriesTree = ($categoryModel);

        return $this->categoriesTree;
    }

    /**
     * @return string
     */
    public function saveCategoriesTree()
    {
        try {
            $this->categoriesTree = [];
            $categories = $this->getCategories();
            $parser = $this->parser->create();
            if (isset($categories) and !empty($categories)) {
                $categories = $parser->loadXML($categories)->xmlToArray();
            }
            $preparedArray = [];
            if ($this->categoriesFactory->create()
                    ->getCollection()->getSize() == 0) {
                $categoryModel = $this->categoriesFactory->create();
                foreach ($categories as $category) {
                    if (isset($category['s:Body']['GetAllowedCategoryTreeResponse']
                        ['GetAllowedCategoryTreeResult']['CategoryTree']
                        ['ChildrenCategoryList']['CategoryTree'])) {
                        foreach ($category['s:Body']
                                 ['GetAllowedCategoryTreeResponse']
                                 ['GetAllowedCategoryTreeResult']['CategoryTree']
                                 ['ChildrenCategoryList']
                                 ['CategoryTree'] as $key => $value) {
                            $name = "";
                            $name = $value['Name'];
                            if (isset($value['ChildrenCategoryList']
                                ['CategoryTree']['Code'])) {
                                $value['ChildrenCategoryList']
                                ['CategoryTree'][] =
                                    $value['ChildrenCategoryList']['CategoryTree'];
                            }
                            foreach ($value['ChildrenCategoryList']
                                     ['CategoryTree'] as $key1 => $value1) {
                                $tempName =
                                    isset($value1['Name']) ? $value1['Name'] : '';
                                $namePath1 = $name . " | " . $tempName;
                                if (isset($value1['Code'])
                                    && !is_array($value1['Code'])
                                    && $value1['Code'] != "") {
                                    $value1['Path'] = $namePath1;
                                    $preparedArray[] = $value1;
                                    continue;
                                }
                                if (!isset($value1['ChildrenCategoryList']['CategoryTree'])) {
                                    continue;
                                }
                                if (isset($value1['ChildrenCategoryList']
                                    ['CategoryTree']['Code'])) {
                                    $value1['ChildrenCategoryList']
                                    ['CategoryTree'][] =
                                        $value1['ChildrenCategoryList']
                                        ['CategoryTree'];
                                }
                                foreach ($value1['ChildrenCategoryList']['CategoryTree'] as $key2 => $value2) {
                                    $tempName = isset($value2['Name']) ? $value2['Name'] : '';
                                    $namePath2 = $namePath1 . " | " . $tempName;
                                    if (isset($value2['Code']) && !is_array($value2['Code']) && $value2['Code'] != "") {
                                        $value2['Path'] = $namePath2;
                                        $preparedArray[] = $value2;
                                        continue;
                                    }
                                    if (!isset($value2['ChildrenCategoryList']['CategoryTree'])) {
                                        continue;
                                    }
                                    if (isset($value2['ChildrenCategoryList']['CategoryTree']['Code'])) {
                                        $value2['ChildrenCategoryList']['CategoryTree'][] =
                                            $value2['ChildrenCategoryList']['CategoryTree'];
                                    }

                                    foreach ($value2['ChildrenCategoryList']['CategoryTree'] as $key3 => $value3) {
                                        $tempName = isset($value3['Name']) ? $value3['Name'] : '';
                                        $namePath3 = $namePath2 . " | " . $tempName;
                                        if (isset($value3['Code']) && !is_array($value3['Code'])
                                            && $value3['Code'] != "") {
                                            $value3['Path'] = $namePath3;
                                            $preparedArray[] = $value3;
                                            continue;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $item = [];
                foreach ($preparedArray as $value) {
                    $item['code'] = $value['Code'];
                    $item['path'] = $value['Path'];
                    $item['label'] = $value['Name'];
                    $item['variant_eligible'] = isset($value['IsVariantProductKindEligible']) ?
                        $value['IsVariantProductKindEligible'] : '';

                    $item['simple_eligible'] = isset($value['IsStandardProductKindEligible']) ?
                        $value['IsStandardProductKindEligible'] : '';

                    $item['ean_optional'] = isset($value['IsEANOptional']) ? $value['IsEANOptional'] :
                        'false';
                    $this->categoriesTree[$value['Code']] = ($item);
                }
                foreach ($this->categoriesTree as $value) {
                    $categoryModel->setData('name', $value['label']);
                    $categoryModel->setData('path', $value['path']);
                    $categoryModel->setData('code', $value['code']);
                    $categoryModel->setData('is_variant_allowed', $value['variant_eligible']);
                    $categoryModel->setData('is_simple_allowed', $value['simple_eligible']);
                    $categoryModel->setData('ean_optional', $value['ean_optional']);
                    $categoryModel->save();
                    $categoryModel->unsetData();
                }
            }
            if ($this->categoriesFactory->create()->getCollection()->getSize() == 0) {
                return 'Failed';
            } else {
                return 'Fetched';
            }
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function getCategories($forceFetch = false)
    {
        $categoryTree = [];
        try {
            $name = 'allcategories' . '.xml';
            $dir = $this->product->createDir();
            $path = $dir['path'] . DS . $name;

            if (file_exists($path) && !$forceFetch) {
                $categoryTree = file_get_contents($path);
            } else {
                $userName = $this->config->getUserName();
                $password = $this->config->getUserPassword();
                $response = $this->apiFactory->create(['username' => $userName, 'password' => $password]);
                $token = $response->init();
                if (!empty($token)) {
                    $response = $response->getProductPoint();
                    file_put_contents($this->getFile($dir['path'], $name), $response->getAllowedCategoryTree());
                    if (file_exists($path)) {
                        $categoryTree = file_get_contents($path);
                    }
                }
            }
            return $categoryTree;
        } catch (\Exception $e) {
            $this->logger->debug(
                "CDiscount\\Sdk\\Product\\getCategories() : Errors: " . var_export($e->getMessage(), true)
            );
            return $categoryTree;
        }
    }

    public function getFile($path, $name = null)
    {
        if (!file_exists($path)) {
            @mkdir($path, 0775, true);
        }

        if ($name != null) {
            $path = $path . DS . $name;

            if (!file_exists($path)) {
                @file($path);
            }
        }

        return $path;
    }

    private function generateCategoriesTree(array $categories = [])
    {
        $data = [];
        foreach ($categories as $category) {
            $item = [];
            $item['value'] = $category['categoryId'];
            $item['leaf'] = $category['leaf'];
            $item['is_active'] = false;
            if ($item['leaf']) {
                $item['is_active'] = true;
            }
            $item['label'] = $category['name'];
            if (isset($category['children']) and !empty($category['children'])) {
                $item['optgroup'] = $this->generateCategoriesTree($category['children']);
            }
            $data[] = $item;
        }
        return $data;
    }
}
