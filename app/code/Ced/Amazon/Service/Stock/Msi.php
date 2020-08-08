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

namespace Ced\Amazon\Service\Stock;

use Ced\Amazon\Api\Service\Stock\StockInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManagerInterface;

class Msi implements StockInterface
{
    /** @var \Magento\InventoryApi\Api\SourceRepositoryInterface $sourceRepository */
    public $sourceRepository;

    /** @var \Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface */
    public $defaultSourceProvider;

    public $searchCriteriaBuilder;

    public $sourceList;

    public function __construct(
        ObjectManagerInterface $objectManager,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->sourceRepository = $objectManager->get("Magento\InventoryApi\Api\SourceRepositoryInterface");
        $this->defaultSourceProvider = $objectManager->get("Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface");
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    private function getActiveSourceList()
    {
        if (!isset($this->sourceList)) {
            $search = $this->searchCriteriaBuilder
                ->addFilter("enabled", "1")
                ->create();
            $this->sourceList = $this->sourceRepository->getList($search);
        }

        return $this->sourceList;
    }

    private function getDefaultSource()
    {
        $default = null;
        $sources = $this->getActiveSourceList();
        /** @var \Magento\InventoryApi\Api\Data\SourceInterface $source */
        foreach ($sources as $source) {
            if ($source->getSourceCode() == "default") {
                $default = $source;
                break;
            }
        }

        return $default;
    }

    private function getAmazonSource()
    {
        $amazon = null;
        $sources = $this->getActiveSourceList();
        /** @var \Magento\InventoryApi\Api\Data\SourceInterface $source */
        foreach ($sources as $source) {
            if ($source->getSourceCode() == "amazon") {
                $default = $source;
                break;
            }
        }

        return $amazon;
    }

    private function getAnySource()
    {
        $any = null;
        $sources = $this->getActiveSourceList();
        /** @var \Magento\InventoryApi\Api\Data\SourceInterface $source */
        foreach ($sources as $source) {
            $any = $source;
            break;
        }

        return $any;
    }

    /**
     * Update Stock by SKU
     * @param string $sku
     * @param integer $qty
     * @return string
     */
    public function updateBySku($sku, $qty)
    {
        $code = $this->defaultSourceProvider->getCode();
        $amazon = $this->getAmazonSource();
        // TODO: impliment
    }

    public function getBySku($sku)
    {
        // TODO: impliment
    }
}
