<?php
/**
 *
 * Copyright © 2015 Iksanika. All rights reserved.
 * See IKS-LICENSE.txt for license details.
 */
namespace Iksanika\Productmanage\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Framework\Controller\ResultFactory;

class UpdateQtyStatus extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $_productPriceIndexerProcessor;

    /**
     * @param Action\Context $context
     * @param Builder $productBuilder
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Product\Builder $productBuilder,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
    ) {
        $this->_productPriceIndexerProcessor = $productPriceIndexerProcessor;
        parent::__construct($context, $productBuilder);
    }

    /**
     * Validate batch of products before theirs status will be set
     *
     * @param array $productIds
     * @param int $status
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _validateMassStatus(array $productIds, $status)
    {
        /*
        if ($status == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            if (!$this->_objectManager->create('Magento\Catalog\Model\Product')->isProductsHasSku($productIds)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please make sure to define SKU values for all processed products.')
                );
            }
        }
\         */
    }

    /**
     * Update product(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $productIds = (array) $this->getRequest()->getParam('product');
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $status = (int) $this->getRequest()->getParam('status');
        
        $is_in_stock = (int) $this->getRequest()->getParam('is_in_stock');
//        $this->indexerRegistry  =   new \Magento\Indexer\Model\IndexerRegistry($this->_objectManager);
        
        $this->indexerRegistry  =   new \Magento\Framework\Indexer\IndexerRegistry($this->_objectManager);
        $this->_stockIndexerProcessor = new \Magento\CatalogInventory\Model\Indexer\Stock\Processor($this->indexerRegistry);

        $productFactory = new \Magento\Catalog\Model\ProductFactory($this->_objectManager);
        // TODO why use ObjectManager?
        // @var \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
        $stockRegistry = $this->_objectManager->create('Magento\CatalogInventory\Api\StockRegistryInterface');
        // @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository
        $stockItemRepository = $this->_objectManager->create('Magento\CatalogInventory\Api\StockItemRepositoryInterface');
        $isUpdated = 0;
        foreach ($productIds as $productId)
        {
            try
            {
                /** @var $product \Magento\Catalog\Model\Product */
                $product = $productFactory->create();
                if($productId) 
                {
                    try {
                        $product->load($productId);
                    } catch (\Exception $e) {
                        $product->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE);
                        $this->logger->critical($e);
                    }

                    $stockItemData = $product->getStockData();
                    $stockItemData['product_id'] = $product->getId();
                        $stockItemDo = $stockRegistry->getStockItem(
                            $productId
    //                            ,
    //                        $this->attributeHelper->getStoreWebsiteId($storeId)
                        );

                    $stockItemDo->setData('is_in_stock', (int) $is_in_stock);
                    $stockRegistry->updateStockItemBySku($product->getData('sku'), $stockItemDo);
                    $isUpdated ++;
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage(__('Error in product %1', $productId).' : '.$e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong while updating the product(s) status. %1', $e->getMessage()));
            }
        }
        $this->messageManager->addSuccessMessage(__('A total %1 out of %1 record(s) have been updated.', $isUpdated, count($productIds)));
//            $this->_productPriceIndexerProcessor->reindexList($productIds);

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('productmanage/*/', ['store' => $storeId]);
    }

    /**
     * Determine if authorized to perform group actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Iksanika_Productmanage::ma_update_is_in_stock');
    }
}
