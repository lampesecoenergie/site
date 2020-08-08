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
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Ui\DataProvider\Product;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

class AddProductStatusCollection implements AddFilterToCollectionInterface
{
    public $request;
    public $catagory;
    public $collectionFactory;

    public function __construct(
        \Magento\Catalog\Model\Category $category,
        \Ced\Cdiscount\Model\ResourceModel\Feeds\CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->request = $request;
        $this->catagory = $category;
    }

    /**
     * @param Collection $collection
     * @param string $field
     * @param null $condition
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        $filters = $this->request->getParam('filters', []);
        if (isset($filters['cdiscount_feed_product']) && !empty($filters['cdiscount_feed_product'])) {
            $ids = $this->collectionFactory->create()
                ->addFieldToFilter('id', ['eq' => $filters['cdiscount_feed_product']])
                ->addFieldToSelect('product_ids')
                ->getFirstItem()
                ->getProductIds();
            $idsToFilter = json_decode($ids, true);
            $collection->addFieldToFilter('entity_id', ['in' => $idsToFilter]);
        }
    }
}
