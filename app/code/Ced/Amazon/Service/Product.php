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

use Ced\Amazon\Api\Service\ConfigServiceInterface;
use Ced\Amazon\Api\Service\ProductServiceInterface;
use Ced\Amazon\Helper\Logger;
use Exception;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Product implements ProductServiceInterface
{
    /** @var ConfigServiceInterface */
    public $config;

    /** @var Logger  */
    public $logger;

    /** @var ProductRepositoryInterface  */
    public $productRepository;

    /** @var ProductInterfaceFactory  */
    public $productFactory;

    /** @var ProductCollectionFactory  */
    public $productCollectionFactory;

    /** @var ProductResource  */
    public $resource;

    public function __construct(
        ConfigServiceInterface $config,
        Logger $logger,
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        ProductCollectionFactory $productCollectionFactory,
        ProductResource $resource
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->resource = $resource;
    }

    /**
     * Find Product By SKU
     * @param $sku
     * @return bool|\Magento\Catalog\Api\Data\ProductInterface
     */
    public function find($sku)
    {
        $product = false;
        try {
            /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
            $product = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            // Finding by custom SKU attribute
            $attribute = $this->config->getAlternateSkuAttribute();
            if (!empty($attribute)) {
                $collection = $this->productCollectionFactory->create()
                    ->addAttributeToSelect(
                        [
                            $attribute,
                            \Magento\Catalog\Api\Data\ProductInterface::SKU,
                        ]
                    )
                    ->addAttributeToFilter($attribute, [
                        ["eq" => $sku],
                        ["like" => "%||{$sku}||%"]
                    ])
                    ->setPageSize(1)
                    ->setCurPage(1);
                if ($collection->getSize() > 0) {
                    /** @var \Magento\Catalog\Model\Product $item */
                    $item = $collection->getFirstItem();
                    try {
                        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
                        $product = $this->productRepository->getById($item->getId());
                    } catch (\Exception $e) {
                        $product = false;
                    }
                }
            }
        } catch (\Exception $e) {
            $product = false;
        }

        return $product;
    }

    /**
     * Create Product
     * @param null|\Amazon\Sdk\Api\Order\ItemList $items
     * @param null|integer $index
     * @return \Magento\Catalog\Api\Data\ProductInterface|boolean
     */
    public function create($items = null, $index = null)
    {
        $product = false;
        try {
            if (isset($items) && isset($index)) {
                /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
                $product = $this->productFactory->create();
                $product->setName($items->getTitle($index));
                $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
                $product->setAttributeSetId($product->getDefaultAttributeSetId());
                $product->setSku($items->getSellerSKU($index));
                $product->setWebsiteIds([1]);
                $product->setVisibility(4);
                $product->setUrlKey($this->parseSeoUrl($items->getSellerSKU($index)));
                $product->setPrice(($items->getItemPrice($index)));
                $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                $product->setData('is_amazon', 1);
                $product->setData('is_salable', 1);
                $product->setStockData(
                    [
                        'use_config_manage_stock' => 1,
                        'is_qty_decimal' => 0,
                        'manage_stock' => 1,
                        'is_in_stock' => 1,
                        'qty' => (int)$items->getQuantityOrdered($index)
                    ]
                );
                $product = $this->productRepository->save($product);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['path' => __METHOD__]);
        }

        return $product;
    }

    /**
     * Update Product Attributes
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param mixed $attributes
     * @throws \Exception
     */
    public function update($product, array $attributes = [])
    {
        foreach ($attributes as $attribute) {
            try {
                $this->resource->saveAttribute($product, $attribute);
            } catch (Exception $e) {
                continue;
            }
        }
    }

    /**
     * Generate Unique SEO Url
     * @param $string
     * @return string
     */
    private function parseSeoUrl($string)
    {
        //Lower case everything
        $string = strtolower((string)$string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);
        return $string . '-' . uniqid();
    }
}
