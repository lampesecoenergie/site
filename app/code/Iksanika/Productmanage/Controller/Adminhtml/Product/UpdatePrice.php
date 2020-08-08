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
use Iksanika\Productmanage\Helper\Data;

class UpdatePrice extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $_productPriceIndexerProcessor;
    
    /**
     * @var \Iksanika\Productmanage\Helper\Data
     */
    protected $_helper;
    

    /**
     * @param Action\Context $context
     * @param Builder $productBuilder
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Iksanika\Productmanage\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Product\Builder $productBuilder,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Iksanika\Productmanage\Helper\Data $helper
    ) {
        $this->_productPriceIndexerProcessor = $productPriceIndexerProcessor;
        
        parent::__construct($context, $productBuilder);
        $this->_helperData = $helper;
        $this->_storeManager = $storeManager;
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
        $storeId = $this->_storeManager->getStore()->getId();
        
        $productFactory = new \Magento\Catalog\Model\ProductFactory($this->_objectManager);
        $columnValuesForUpdate  =   $this->getRequest()->getParam('price');

        $isUpdated = 0;
        foreach ($productIds as $itemId => $productId)
        {
            try {
                $product = $productFactory->create();

                if ($productId) {
                    try {
                        $product->setStoreId($storeId)->load($productId);
                    } catch (\Exception $e) {
                        $product->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE);
                        $this->logger->critical($e);
                    }
                }

                $columnValuesForUpdate = $this->getRequest()->getParam('price');
                $product->setData('price', $this->_helperData->recalculatePrice($product->getData('price'), $columnValuesForUpdate))->save();
                $product->setStoreId($storeId)->save();
                $isUpdated++;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage(__('Error in product %1', $productId).' : '.$e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong while updating the product(s) status. %1', $e->getMessage()));
            }
        }

        $this->messageManager->addSuccessMessage(__('A total %1 out of %2 record(s) have been updated.', $isUpdated, count($productIds)));
        //$this->_productPriceIndexerProcessor->reindexList($productIds);

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
        return $this->_authorization->isAllowed('Iksanika_Productmanage::ma_update_price');
    }
}
