<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_EbayMultiAccount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Massinvpricesync
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\Product
 */
class Massinvpricesync extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;
    /**
     * @var CollectionFactory
     */
    public $catalogCollection;
    /**
     * @var Filter
     */
    public $filter;

    const ADMIN_RESOURCE = 'Ced_EbayMultiAccount::EbayMultiAccount';

    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * Massinvpricesync constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CollectionFactory $collectionFactory,
        \Ced\EbayMultiAccount\Helper\MultiAccount $multiAccountHelper,
        Filter $filter
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->catalogCollection = $collectionFactory;
        $this->filter = $filter;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $productIdsToSync = $configSimpleIds = [];
        $accountId = $this->_session->getAccountId();
        $prodStatusAccAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($accountId);
        $ids = $this->filter->getCollection($this->catalogCollection->create()->addFieldToFilter($prodStatusAccAttr, 4))->getAllIds();
        if (!empty($ids)) {


            $configCollection = $this->catalogCollection->create()
                ->addAttributeToSelect('entity_id')
                ->addAttributeToFilter('entity_id', $ids)
                ->addAttributeToFilter('type_id', array('configurable'));
            $configIds = array_column($configCollection->getData(), 'entity_id');
            $configIds = (array_chunk($configIds, 4));
            foreach ($configIds as $configId) {
                $simplePIds = $this->getSimpleProductIds($configId);
                $simplePIds = (array_chunk($simplePIds, 4));
                $configSimpleIds = array_merge($configSimpleIds, $simplePIds);
            }

            $simpleCollection = $this->catalogCollection->create()
                ->addAttributeToSelect('entity_id')
                ->addAttributeToFilter('entity_id', $ids)
                ->addAttributeToFilter('type_id', array('simple'));
            $simpleIds = array_column($simpleCollection->getData(), 'entity_id');
            $simpleIds = (array_chunk($simpleIds, 4));
            $productids = array_merge($simpleIds, $configSimpleIds);
            foreach ($productids as $prodChunkKey => $prodids) {
                $productids[$prodChunkKey] = array($accountId => $prodids);
            }
            $productIdsToSync = array_merge($productIdsToSync, $productids);

            /*$productids = array_chunk($ids, 4);
            foreach ($productids as $prodChunkKey => $prodids) {
                $productIdsToSync[$prodChunkKey] = array($accountId => $prodids);
            }*/
            $this->_session->setUploadChunks($productIdsToSync);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Ced_EbayMultiAccount::product');
            $resultPage->getConfig()->getTitle()->prepend(__('Sync Price Inventory On eBay'));
            return $resultPage;
        } else {
            $this->messageManager->addErrorMessage(__('No product available for Inventory sync.'));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }

    public function getSimpleProductIds($configProductIds)
    {
        $simpleIds = array();
        foreach ($configProductIds as $configProductId) {
            $product = $this->catalogCollection->create()
                ->addAttributeToFilter('entity_id', $configProductId)
                ->getFirstItem();
            if ($product == NULL) {
                continue;
            }
            if ($product->getTypeId() == 'configurable') {

                $productType = $product->getTypeInstance();
                $products = $productType->getUsedProducts($product);
                foreach ($products as $chProduct) {
                    $simpleIds[] = $chProduct->getId();
                }
            }
        }
        return $simpleIds;
    }
}
