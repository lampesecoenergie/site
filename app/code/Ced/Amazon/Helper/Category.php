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
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Helper;

class Category
{
    const DEFAULT_ATTRIBUTE_MAPPING = [
        'SKU' => 'sku',
        'DescriptionData_Title' => 'name',
        'DescriptionData_Brand' => 'brand',
        'StandardProductID_Value' => 'barcode',
        'DescriptionData_Description' => 'description',
        //'DescriptionData_MfrPartNumber' => 'mpn',
    ];

    /** @var \Magento\Framework\ObjectManagerInterface  */
    public $objectManager;

    /** @var Logger  */
    public $logger;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\Amazon\Helper\Logger $logger
    ) {
        $this->objectManager = $objectManager;
        $this->logger = $logger;
    }

    /**
     * Get category-wise attributes
     * @param $categoryName
     * @param $subCategoryName
     * @param array $params
     * @param boolean $barcode
     * @return array
     */
    public function getAttributes($categoryName, $subCategoryName, $params = [], $barcode = false)
    {
        $className = '\Amazon\Sdk\Product\Category\\' . $categoryName;
        $attributes = [];
        try {
            /** @var \Amazon\Sdk\Product\CategoryInterface $category */
            $category = $this->objectManager
                ->create(
                    $className,
                    ['subCategory' => $subCategoryName]
                );
            $category->setBarcodeExemption($barcode);
            $attributes = $category->getAttributes($params);
            foreach ($attributes as $id => &$attribute) {
                if (isset(self::DEFAULT_ATTRIBUTE_MAPPING[$id])) {
                    $attribute['magento_attribute_code'] = self::DEFAULT_ATTRIBUTE_MAPPING[$id];
                }
            }
        } catch (\ReflectionException $e) {
            $this->logger->debug(
                "Amazon category class {$className} missing.",
                ['path' => __METHOD__, 'message' => $e->getMessage()]
            );
        } catch (\Exception $e) {
            $this->logger->debug(
                'Amazon category attribute get failed.',
                ['path' => __METHOD__, 'message' => $e->getMessage()]
            );
        }

        return $attributes;
    }
}
