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

class CategoryAdd extends \Magento\Catalog\Controller\Adminhtml\Product
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
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        \Iksanika\Productmanage\Helper\Data $helper
    ) {
        $this->_productPriceIndexerProcessor = $productPriceIndexerProcessor;
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

        // categories which was not found in verification process
        $categoryNotFounded = array();

        //
        $updatedProducts = array();

        if (is_array($productIds))
        {

            $columnValuesForUpdate  =   $this->getRequest()->getParam('category');
            $categoryIdsAdd         =   explode(',', $columnValuesForUpdate);

            $category = $this->_objectManager->create('Magento\Catalog\Model\Category');
            $verifiedCategories = array_flip($category->verifyIds($categoryIdsAdd));

            $isUpdated = 0;
            foreach($categoryIdsAdd as $categoryId)
            {
                try
                {
                    if($categoryId && isset($verifiedCategories[$categoryId]))
                    {
                        $category = $this->_objectManager->create('Magento\Catalog\Model\Category');
                        $category->setStoreId($storeId);
                        $category->load($categoryId);

                        // get list of current products assigned to selected category
                        $categoryProductsCurrent = $category->getProductCollection();
                        $categoryProductsCurrentIds = array();
                        foreach($categoryProductsCurrent as $product)
                        {
                            $categoryProductsCurrentIds[$product->getId()] = $product->getId();
                        }

                        // generate aggregated list of products
                        $productIds = array_flip($productIds);
                        $aggregated = array();
                        foreach($categoryProductsCurrentIds as $productId => $value)
                        {
                            $aggregated[$productId] = $productId;
                        }
                        foreach($productIds as $productId => $value)
                        {
                            $aggregated[$productId] = $productId;
                        }

                        // save products by assigning them to particular category
                        if(!$category->getProductsReadonly())
                        {
                           $category->setPostedProducts($aggregated);
                        }

                        // save update data
                        $category->setStore($storeId)->save();
                        $isUpdated ++;

                        foreach($aggregated as $productId => $value)
                        {
                            $updatedProducts[$productId] = $productId;
                        }

                    }else
                    if(!isset($verifiedCategories[$categoryId]))
                    {
                        $categoryNotFounded[] = $categoryId;
                    }


                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('Something went wrong while updating the product(s) status. %1', $e->getMessage()));
                }

            }
//                $this->messageManager->addSuccess(__('A total of %1 product(s) have been updated.', (count($updatedProducts))));
            $this->messageManager->addSuccessMessage(__('A total of %1 product(s) have been updated.', (count($productIds))));
            if(count($categoryNotFounded))
            {
                $categoryNotFoundedStr = implode(', ', $categoryNotFounded);
                $this->messageManager->addErrorMessage(__('A category with index %1 does not exist, please, check if category ID was speicifed correctly.'), $categoryNotFoundedStr);
            }
        }else
        {
            $this->messageManager->addErrorMessage(__('Please select product(s)').'. '.__('You should select checkboxes for each product row which should be updated. You can click on checkboxes or use CTRL+Click on product row which should be selected.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('productmanage/*/', ['_current' => true, '_query' => 'st=1']); //'store' => $storeId]
    }



    /**
     * Update product(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute_initialVersion()
    {
        $productIds = (array) $this->getRequest()->getParam('product');
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        
        if (is_array($productIds)) 
        {
            $productFactory = new \Magento\Catalog\Model\ProductFactory($this->_objectManager);
            try {
                $columnValuesForUpdate  =   $this->getRequest()->getParam('category');
                $categoryIdsAdd         =   explode(',', $columnValuesForUpdate);
                
                foreach($productIds as $itemId => $productId) 
                {
                    $product = $productFactory->create();
                    if ($productId) {
                        try {
                            $product->load($productId);
                        } catch (\Exception $e) {
                            $product->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE);
                            $this->logger->critical($e);
                        }
                    }
                    $categoryIdsExist   =   $product->getCategoryIds();
                    $product->setCategoryIds(array_merge($categoryIdsAdd, $categoryIdsExist));
                    $product->setStoreId($this->_helperData->getStoreId())->save();
                }
                
                $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been updated.', count($productIds)));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong while updating the product(s) status.'));
            }
        }else
        {
            $this->messageManager->addErrorMessage($this->__('Please select product(s)').'. '.$this->__('You should select checkboxes for each product row which should be updated. You can click on checkboxes or use CTRL+Click on product row which should be selected.'));
        }
//        $this->_redirect('productmanage/*/index', array('_current' => true, '_query' => 'st=1'));
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('productmanage/*/', ['_current' => true, '_query' => 'st=1']); //'store' => $storeId]
    }

    /**
     * Determine if authorized to perform group actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Iksanika_Productmanage::ma_category_add');
    }
}
