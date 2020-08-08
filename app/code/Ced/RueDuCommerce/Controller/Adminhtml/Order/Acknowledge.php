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
 * @package   Ced_RueDuCommerce
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RueDuCommerce\Controller\Adminhtml\Order;

class Acknowledge extends \Magento\Backend\App\Action
{
    const CHUNK_SIZE = 10;
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_RueDuCommerce::rueducommerce_orders';
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    public $resultRedirectFactory;
    /**
     * @var \Ced\RueDuCommerce\Helper\Order
     */
    public $orderHelper;

    /**
     * @var \Ced\RueDuCommerce\Helper\Product
     */
    public $rueducommerce;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * Fetch constructor.
     *
     * @param \Magento\Backend\App\Action\Context                  $context
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Ced\RueDuCommerce\Helper\Order                             $orderHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ced\RueDuCommerce\Helper\Order $orderHelper,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ced\RueDuCommerce\Model\Orders $collection,
        \Ced\RueDuCommerce\Helper\Product $product,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->orderHelper = $orderHelper;
        $this->filter = $filter;
        $this->orders = $collection;
        $this->rueducommerce = $product;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->session =  $context->getSession();
        $this->registry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if (!$this->rueducommerce->checkForConfiguration()) {
            $this->messageManager->addErrorMessage(
                __('Products Upload Failed. RueDuCommerce API not enabled or Invalid. Please check RueDuCommerce Configuration.')
            );
            $resultRedirect = $this->resultFactory->create('redirect');
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        // case 2 ajax request for chunk processing
        $batchId = $this->getRequest()->getParam('batchid');
        if (isset($batchId)) {
            $resultJson = $this->resultJsonFactory->create();
            $orderIds = $this->session->getRueDuCommerceOrders();
            $response = $this->orderHelper->acknowledgeOrders($orderIds[$batchId]);
            if (isset($orderIds[$batchId]) && $response) {
                return $resultJson->setData(
                    [
                        'success' => count($orderIds[$batchId]) . "Order Acknowledge Successfully",
                        'messages' => $response//$this->registry->registry('rueducommerce_product_errors')
                    ]
                );
            }
            return $resultJson->setData(
                [
                    'error' => count($orderIds[$batchId]) . "Order Acknowledge Failed",
                    'messages' => $this->registry->registry('rueducommerce_order_errors'),
                ]
            );
        }

        // case 3 normal uploading and chunk creating
        $collection = $this->filter->getCollection($this->orders->getCollection());
        $orderIds = $collection->getColumnValues('rueducommerce_order_id');

        if (count($orderIds) == 0) {
            $this->messageManager->addErrorMessage('No Order selected to acknowledge.');
            $resultRedirect = $this->resultFactory->create('redirect');
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        // case 3.1 normal uploading if current ids are less than chunk size.
        if (count($orderIds) <= self::CHUNK_SIZE) {
            $response = $this->orderHelper->acknowledgeOrders($orderIds);
            if ($response) {
                $this->messageManager->addSuccessMessage(count($orderIds) . ' Order(s) Acknowledged Successfully');
            } else {
                $message = 'Order(s) Acknowledge Failed.';
                $errors = $this->registry->registry('rueducommerce_order_errors');
                if (isset($errors)) {
                    $message = "Order(s) Acknowledge Failed. \nErrors: " . (string)json_encode($errors);
                }
                $this->messageManager->addError($message);
            }

            $resultRedirect = $this->resultFactory->create('redirect');
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
        // case 3.2 normal uploading if current ids are more than chunk size.
        $orderIds = array_chunk($orderIds, self::CHUNK_SIZE);
        $this->registry->register('orderids', count($orderIds));
        $this->session->setRueDuCommerceOrders($orderIds);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_RueDuCommerce::RueDuCommerce');
        $resultPage->getConfig()->getTitle()->prepend(__('Acknowledge Orders'));
        return $resultPage;
    }
}
