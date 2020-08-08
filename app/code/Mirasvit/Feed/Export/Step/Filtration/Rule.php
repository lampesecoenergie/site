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



namespace Mirasvit\Feed\Export\Step\Filtration;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Mirasvit\Feed\Export\Context;
use Mirasvit\Feed\Export\Step\AbstractStep;
use Mirasvit\Feed\Model\RuleFactory;

class Rule extends AbstractStep
{
    private $ruleFactory;

    private $productCollectionFactory;

    private $ruleId;

    public function __construct(
        RuleFactory $ruleFactory,
        ProductCollectionFactory $productCollectionFactory,
        Context $context,
        $data = []
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->context = $context;

        $this->ruleId = $data['rule_id'];

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeExecute()
    {
        parent::beforeExecute();

        $this->index = 0;
        $this->length = $this->getProductCollection()->getSize();
        $this->ruleFactory->create()->load($this->ruleId)
            ->clearProductIds();
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->isReady()) {
            $this->beforeExecute();
        }

        $rule = $this->ruleFactory->create()->load($this->ruleId);

        $validIds = [];

        $lastId = 0;
        while (!$this->isCompleted()) {
            $collection = $this->getProductCollection();

            if ($lastId) {
                $collection->getSelect()
                    ->where('e.entity_id > ?', $lastId)
                    ->limit(100, 0);
            } else {
                $collection->getSelect()
                    ->limit(100, $this->index);
            }

            $startIndex = $this->index;

            foreach ($collection as $product) {
                $lastId = $product->getId();
                $this->index++;

                if ($rule->getConditions()->validate($product)) {
                    $validIds[] = $product->getId();
                }

                if ($this->context->isTimeout()) {
                    break 2;
                }
            }
            #sometimes collection getSize not equal real number of items
            if ($startIndex == $this->index) {
                $this->length = $this->index;
            }
        }

        $rule->saveProductIds($validIds);

        if ($this->isCompleted()) {
            $this->afterExecute();
        }
    }

    /**
     * Product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function getProductCollection()
    {
        $collection = $this->productCollectionFactory->create()
            ->addStoreFilter($this->context->getFeed()->getStoreId())
            ->setStoreId($this->context->getFeed()->getStoreId())
            ->setFlag('has_stock_status_filter', true);

        $collection->getSelect()->order('e.entity_id asc');

        // add base attributes - improve simple filters time
        $collection->addAttributeToSelect('status')
            ->addAttributeToSelect('visibility');

        return $collection;
    }
}
