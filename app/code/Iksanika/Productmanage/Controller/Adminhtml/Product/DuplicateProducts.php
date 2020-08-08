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

class DuplicateProducts extends \Magento\Catalog\Controller\Adminhtml\Product
{

    /**
     * @var \Magento\Catalog\Model\Product\Copier
     */
    protected $productCopier;
    
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $_productPriceIndexerProcessor;

    /**
     * @param Action\Context $context
     * @param Builder $productBuilder
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
     * @param \Iksanika\Productmanage\Helper\Data $helper
     * @param \Magento\Catalog\Model\Product\Copier $productCopier
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Product\Builder $productBuilder,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        \Iksanika\Productmanage\Helper\Data $helper,
        \Magento\Catalog\Model\Product\Copier $productCopier
    ) {
        $this->_productPriceIndexerProcessor = $productPriceIndexerProcessor;
        $this->productCopier = $productCopier;
        parent::__construct($context, $productBuilder);
        $this->_helperData = $helper;
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
        
        $columnValuesForLevel = $this->getRequest()->getParam('qty');
//        $this->indexerRegistry  =   new \Magento\Indexer\Model\IndexerRegistry($this->_objectManager);
        $this->indexerRegistry  =   new \Magento\Framework\Indexer\IndexerRegistry($this->_objectManager);
        $this->_stockIndexerProcessor = new \Magento\CatalogInventory\Model\Indexer\Stock\Processor($this->indexerRegistry);

        $productFactory = new \Magento\Catalog\Model\ProductFactory($this->_objectManager);
        $isUpdated = 0;
        foreach ($productIds as $productId)
        {
            try
            {
                $product = $productFactory->create();
                if ($productId)
                {
                    try {
                        $product->setStoreId($this->_helperData->getStoreId())->load($productId);
                    } catch (\Exception $e) {
                        $product->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE);
                        $this->logger->critical($e);
                    }
                    //$product    =   Mage::getModel('catalog/product')->setStoreId(Mage::helper('productupdater')->getStoreId())->load($productId);

                    //$product->setSku($product->getSku().'-#1');
                    $newProduct = $this->productCopier->copy($product);
                    $isUpdated ++;
                    /*
                    $clone      =   $product->duplicate();
                    $clone->setSku($product->getSku().'-#1');
                    $clone->setVisibility($product->getVisibility());
                    $clone->setStatus($product->getStatus());
                    $clone->setTaxClassId($product->getTaxClassId());
                    $clone->setCategoryIds($product->getCategoryIds());
                    try {
                        $clone->getResource()->save($clone);
                    }catch(Exception $e)
                    {
                        Mage::logException($e);
                    }
                    */
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong while updating the product(s) status. %1', $e->getMessage()));
            }
        }
        $this->messageManager->addSuccessMessage(__('A total %1 out of %2 record(s) have been duplicated.', $isUpdated, count($productIds)));


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
        return $this->_authorization->isAllowed('Iksanika_Productmanage::ma_duplicate');
    }
}
