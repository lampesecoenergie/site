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
 * @package   Ced_m2.1.9
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Controller\Adminhtml\Product;


use Magento\Framework\App\ResponseInterface;

class ProductStatus extends \Magento\Framework\App\Action\Action
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

    public $redirectFactory;

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
        $this->session = $context->getSession();
        $this->registry = $registry;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * @return $this|ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $batchId = $this->getRequest()->getParam('batchid');
        $redirect = $this->redirectFactory->create();

        if (!$this->cdiscount->checkForConfiguration()) {
            $this->messageManager->addErrorMessage(
                __('Products Upload Failed. Cdiscount API not enabled or Invalid. Please check Cdiscount Configuration.')
            );
            return $redirect->setPath(\Ced\Cdiscount\Controller\Adminhtml\Product\Validate::REDIRECT_PATH);
        }

        if (isset($batchId)) {
            $resultJson = $this->resultJsonFactory->create();
            $productIds = $this->session->getCdiscountProducts();
            $response = $this->cdiscount->getUploadedProductStatus($productIds[$batchId]);
            if (isset($productIds[$batchId]) && $response) {
                return $resultJson->setData(
                    [
                        'success' => count($productIds[$batchId]) . " Product(s) Status Syncing Successfull",
                        'messages' => $response//$this->registry->registry('cdiscount_product_errors')
                    ]
                );
            }
            return $resultJson->setData(
                [
                    'error' => count($productIds[$batchId]) . " Product(s) Status Syncing Failed",
                    'messages' => $this->registry->registry('cdiscount_product_errors'),
                ]
            );
        }

        // case 3 normal uploading and chunk creating
        $collection = $this->filter->getCollection($this->catalogCollection->getCollection());
        $productIds = $collection->getAllIds();

        if (count($productIds) == 0) {
            $this->messageManager->addErrorMessage('No Product selected to sync.');
            return $redirect->setPath(\Ced\Cdiscount\Controller\Adminhtml\Product\Validate::REDIRECT_PATH);
        }

        // case 3.1 normal uploading if current ids are equal to chunk size.
        if (count($productIds) <= self::CHUNK_SIZE) {
            $response = $this->cdiscount->getUploadedProductStatus($productIds);
            if ($response) {
                $this->messageManager->addSuccessMessage(count($productIds) . ' Product(s) Status Syncing Successfull');
            } else {
                $message = 'Product(s) Status Syncing Failed.';
                $errors = $this->registry->registry('cdiscount_product_errors');
                if (isset($errors)) {
                    $message = "Product(s) Status Syncing Failed. \nErrors: " . (string)json_encode($errors);
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
        $resultPage->getConfig()->getTitle()->prepend(__('Product Status Syncing'));
        return $resultPage;
    }
}