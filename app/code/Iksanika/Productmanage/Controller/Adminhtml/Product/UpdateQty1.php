<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iksanika\Productmanage\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Framework\Controller\ResultFactory;

class UpdateQty extends \Magento\Catalog\Controller\Adminhtml\Product
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
        
        $qty = (float) $this->getRequest()->getParam('qty');
        $this->indexerRegistry  =   new \Magento\Indexer\Model\IndexerRegistry($this->_objectManager);
        $this->_stockIndexerProcessor = new \Magento\CatalogInventory\Model\Indexer\Stock\Processor($this->indexerRegistry);
        try {
                $productFactory = new \Magento\Catalog\Model\ProductFactory($this->_objectManager);
                // TODO why use ObjectManager?
               // @var \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry 
                $stockRegistry = $this->_objectManager
                    ->create('Magento\CatalogInventory\Api\StockRegistryInterface');
                // @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository
                $stockItemRepository = $this->_objectManager
                    ->create('Magento\CatalogInventory\Api\StockItemRepositoryInterface');
                foreach ($productIds as $productId) {
            $product = $productFactory->create();
            if ($productId) {
                try {
                    $product->load($productId);
                } catch (\Exception $e) {
                    $product->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE);
                    $this->logger->critical($e);
                }
            }
                
            {
                $stockItemData = $product->getStockData();
                $stockItemData['product_id'] = $product->getId();
                    $stockItemDo = $stockRegistry->getStockItem(
                        $productId
//                            ,
//                        $this->attributeHelper->getStoreWebsiteId($storeId)
                    );
                    

$stockItemDo->setData('qty', (string) $qty);
                    
                    
$stockRegistry->updateStockItemBySku($product->getData('sku'), $stockItemDo);
/*
                if (!isset($stockItemData['website_id'])) {
                    $stockItemData['website_id'] = $this->stockConfiguration->getDefaultWebsiteId();
                }
                $stockItemData['stock_id'] = $stockRegistry->getStock($stockItemData['website_id'])->getStockId();

                foreach ($this->paramListToCheck as $dataKey => $configPath) {
                    if (null !== $product->getData($configPath['item']) && null === $product->getData($configPath['config'])) {
                        $stockItemData[$dataKey] = false;
                    }
                }

                $originalQty = $product->getData('stock_data/original_inventory_qty');
                if (strlen($originalQty) > 0) {
                    $stockItemData['qty_correction'] = (isset($stockItemData['qty']) ? $stockItemData['qty'] : 0)
                        - $originalQty;
                }

                // todo resolve issue with builder and identity field name
                $stockItem = $this->stockRegistry->getStockItem($stockItemData['product_id'], $stockItemData['website_id']);

                $stockItem->addData($stockItemData);
                $this->stockItemRepository->save($stockItem);
 * 
 */
            }
//die();
            /*
*/



                }

            
//            $this->_validateMassStatus($productIds, $status);
//            $this->_objectManager->get('Magento\Catalog\Model\Product\Action')
//                ->updateAttributes($productIds, ['status' => $status], $storeId);
//            $this->_objectManager->get('Magento\Catalog\Model\Product\Action')->getOrigData();
//            var_dump();
            
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been updated.', count($productIds)));
//            $this->_productPriceIndexerProcessor->reindexList($productIds);
            
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while updating the product(s) status. %1', $e->getMessage()));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('productmanage/*/', ['store' => $storeId]);
    }
}
