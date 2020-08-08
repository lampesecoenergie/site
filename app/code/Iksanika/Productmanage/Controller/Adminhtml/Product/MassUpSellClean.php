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

class MassUpSellClean extends \Magento\Catalog\Controller\Adminhtml\Product
{

    /**
     * @param Action\Context $context
     * @param Builder $productBuilder
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Product\Builder $productBuilder,

        \Iksanika\Productmanage\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context, $productBuilder);

        $this->_helperData = $helper;
        $this->productRepository = $productRepository;
        $this->_storeManager = $storeManager;
    }

    /**
     * Update product(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $productIds = (array) $this->getRequest()->getParam('product');

        $storeId = $this->getRequest()->getParam('store', 0);
        $store = $this->_storeManager->getStore($storeId);
        $this->_storeManager->setCurrentStore($store->getCode());

        if(is_array($productIds))
        {
            $isUpdated = 0;
            foreach($productIds as $itemId => $productId)
            {
                try
                {
                        if($productId)
                        {
                            try {
                                $product = $this->productRepository->getById($productId, true, $storeId);
                            } catch (\Exception $e) {
                                $this->logger->critical($e);
                            }
                        }
                        $product->setProductLinks($this->_helperData->removeProductLinksByType($product, 'upsell'));
                        $product->save();
                       $isUpdated ++;
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('Error in product %1', $productId).' : '.$e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('Something went wrong while updating the product(s) status. %1', $e->getMessage()));
                }
            }
            $this->messageManager->addSuccessMessage(__('Total of %1 out of %2 product(s) were successfully cleaned from up sell product(s).', $isUpdated, count($productIds)));
        }else
        {
            $this->messageManager->addErrorMessage(__('Please select product(s)').'. '.__('You should select checkboxes for each product row which should be updated. You can click on checkboxes or use CTRL+Click on product row which should be selected.'));
        }
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
        return $this->_authorization->isAllowed('Iksanika_Productmanage::ma_up_sells_clear');
    }
}
