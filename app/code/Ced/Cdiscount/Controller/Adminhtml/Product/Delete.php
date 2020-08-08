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
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Controller\Adminhtml\Product;

/**
 * Class Delete
 *
 * @package Ced\Cdiscount\Controller\Adminhtml\Product
 */
class Delete extends \Magento\Backend\App\Action
{
    const CHUNK_SIZE = 50;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;
    /**
     * @var \Ced\Cdiscount\Helper\Product
     */
    public $cdiscount;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    public $catalogCollection;
    /**
     * @var \Magento\Backend\Model\Session
     */
    public $session;
    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    public $redirectFactory;

    /**
     * Delete constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Catalog\Model\Product $collection
     * @param \Ced\Cdiscount\Helper\Product $product
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Catalog\Model\Product $collection,
        \Ced\Cdiscount\Helper\Product $product,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->catalogCollection = $collection;
        $this->cdiscount = $product;
        $this->session =  $context->getSession();
        $this->registry = $registry;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $redirect = $this->redirectFactory->create();

        if (!$this->cdiscount->checkForConfiguration()) {
            $this->messageManager->addErrorMessage(
                __('Products Delete Failed. Cdiscount API not enabled or Invalid. Please check Cdiscount Configuration.')
            );
            return $redirect->setPath(\Ced\Cdiscount\Controller\Adminhtml\Product\Validate::REDIRECT_PATH);
        }

        // case 2 Ajax request for chunk processing
        $batchId = $this->getRequest()->getParam('batchid');

        if (isset($batchId)) {
            $resultJson = $this->resultJsonFactory->create();
            $productIds = $this->session->getCdiscountProducts();
            $response = $this->cdiscount->deleteProducts($productIds[$batchId]);
            if (isset($productIds[$batchId]) && $response) {
                return $resultJson->setData(
                    [
                    'success' => count($productIds[$batchId]) . " Product(s) Unpublished successfully",
                    'messages' => $response//$this->registry->registry('cdiscount_product_errors')
                    ]
                );
            }
            return $resultJson->setData(
                [
                'error' => count($productIds[$batchId]) . " Product(s) Unpublish Failed",
                'messages' => $this->registry->registry('cdiscount_product_errors'),
                ]
            );
        }

        // case 3 normal uploading and chunk creating
        $collection = $this->filter->getCollection($this->catalogCollection->getCollection());
        $productIds = $collection->getAllIds();

        if (count($productIds) == 0) {
            $this->messageManager->addErrorMessage('No Product selected to unpublish.');
            return $redirect->setPath(\Ced\Cdiscount\Controller\Adminhtml\Product\Validate::REDIRECT_PATH);
        }

        // case 3.1 normal uploading if current ids are equal to chunk size.
        if (count($productIds) <= self::CHUNK_SIZE) {
            $response = $this->cdiscount->deleteProducts($productIds);
            if ($response) {
                if (is_bool($response)) {
                    $this->messageManager->addSuccessMessage(count($productIds) . ' Product(s) Unpublished Successfully');
                } else{
                    $this->messageManager->addSuccessMessage($response);
                }
            } else {
                $message = 'Product(s) Unpublish Failed.';
                $errors = $this->registry->registry('cdiscount_product_errors');
                if (isset($errors)) {
                    $message = "Product(s) Unpublish Failed. \nErrors: " . (string)json_encode($errors);
                }
                $this->messageManager->addError($message);
            }
            return $redirect->setPath(\Ced\Cdiscount\Controller\Adminhtml\Product\Validate::REDIRECT_PATH);
        }

        $productIds = array_chunk($productIds, self::CHUNK_SIZE);
        $this->registry->register('productids', count($productIds));
        $this->session->setCdiscountProducts($productIds);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Cdiscount::Cdiscount');
        $resultPage->getConfig()->getTitle()->prepend(__('Unpublish Products'));
        return $resultPage;
    }
}
