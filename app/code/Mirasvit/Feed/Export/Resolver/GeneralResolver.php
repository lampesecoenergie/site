<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Export\Resolver;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mirasvit\Feed\Export\Context;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GeneralResolver extends AbstractResolver
{
    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var ReviewCollectionFactory
     */
    protected $reviewCollectionFactory;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    public function __construct(
        ResourceConnection $resource,
        ProductCollectionFactory $productCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        ReviewCollectionFactory $reviewCollectionFactory,
        TimezoneInterface $timezone,
        Pool $pool,
        Context $context,
        ObjectManagerInterface $objectManager
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->timezone = $timezone;
        $this->pool = $pool;
        $this->resource = $resource;

        parent::__construct($context, $objectManager);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [];
    }

    /**
     * Store model
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->context->getFeed()->getStore();
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->timezone->date()->format("d.m.Y H:i:s");
    }

    /**
     * Collection of filtered products
     *
     * @param null $object
     * @param array $args
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getProducts($object = null, $args = [])
    {
        $collection = $this->productCollectionFactory->create()
            ->addStoreFilter();

        if ($this->context->isTestMode()) {
            $collection->getSelect()->limit(10);

            /** @var \Magento\Framework\App\RequestInterface $request */
            $request = $this->objectManager->get(\Magento\Framework\App\RequestInterface::class);

            if ($request->getParam('preview_ids')) {
                $ids = explode(',', $request->getParam('preview_ids'));
            } else {
                $random = $this->resource->getConnection()->fetchAll(
                    $collection->getSelect()
                        ->limit(10)
                        ->order('rand()')
                );

                $ids = array_map(function ($item) {
                    return $item['entity_id'];
                }, $random);
            }

            $ids[] = 0;

            $collection->getSelect()
                ->reset('limitcount')
                ->reset('limitoffset')
                ->reset('order');

            $collection->addFieldToFilter('entity_id', $ids);
        } else {
            if (count($this->context->getFeed()->getRuleIds())) {
                $collection->getSelect()->joinLeft(
                    ['rule' => $this->resource->getTableName('mst_feed_feed_product')],
                    'e.entity_id=rule.product_id',
                    []
                )->where('rule.feed_id = ?', $this->context->getFeed()->getId())
                    ->where('rule.is_new = 1');
            }

            $collection->setFlag('has_stock_status_filter', true);
            if (isset($args['index'])) {
                $collection->getSelect()->limit($args['length'], $args['index']);
                $collection->setStore($this->context->getFeed()->getStore())
                    ->load();
            }
        }

        return $collection;
    }

    /**
     * Collection of categories
     *
     * @param null $object
     * @param array $args
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCategories($object = null, $args = [])
    {
        $collection = $this->categoryCollectionFactory->create()
            ->addIsActiveFilter();

        if ($this->context->isTestMode()) {
            $collection->getSelect()->limit(10);
        }

        return $collection;
    }

    /**
     * Collection of reviews
     *
     * @param null $object
     * @param array $args
     * @return \Magento\Review\Model\ResourceModel\Review\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getReviews($object = null, $args = [])
    {
        $collection = $this->reviewCollectionFactory->create()
            ->addStoreFilter($this->context->getFeed()->getStore()->getId())
            ->addStatusFilter(1);

        if ($this->context->isTestMode()) {
            $collection->getSelect()->limit(10);

            $random = $this->resource->getConnection()->fetchAll(
                $collection->getSelect()
                    ->limit(10)
                    ->order('rand()')
            );
            $ids = array_map(function ($item) {
                return $item['review_id'];
            }, $random);

            $collection->getSelect()
                ->reset('limitcount')
                ->reset('limitoffset')
                ->reset('order');

            $collection->addFieldToFilter('main_table.review_id', $ids);
        }

        $collection->addRateVotes();

        return $collection;
    }
}
