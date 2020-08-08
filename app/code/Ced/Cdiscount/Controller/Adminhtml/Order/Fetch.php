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

namespace Ced\Cdiscount\Controller\Adminhtml\Order;

class Fetch extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    public $resultRedirectFactory;
    /**
     * @var \Ced\Cdiscount\Helper\Order
     */
    public $orderHelper;

    /**
     * @var \Ced\Cdiscount\Model\ResourceModel\OrderFailed\CollectionFactory
     */
    public $failedOrder;
    /**
     * Fetch constructor.
     *
     * @param \Magento\Backend\App\Action\Context                  $context
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Ced\Cdiscount\Helper\Order                             $orderHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Ced\Cdiscount\Helper\Order $orderHelper,
        \Ced\Cdiscount\Model\ResourceModel\OrderFailed\CollectionFactory $collection
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->orderHelper = $orderHelper;
        $this->failedOrder = $collection;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute()
    {
        $status = $this->orderHelper->importOrders();
        $failedCounts = $this->failedOrder->create()->getSize();
        $result = $this->resultRedirectFactory->create();
        $result->setPath('cdiscount/order/index');
        if ($failedCounts == 0 && $status === true) {
            $this->messageManager->
            addSuccessMessage(" There Are Failed 
            Cdiscount Orders View Them In Failed Orders Tab");
            return $result;
        }
        if ($status === true) {
            $this->messageManager->
            addSuccessMessage(" New Cdiscount Orders fetched successfully");
        } else {
            $this->messageManager->addSuccessMessage("No New Cdiscount Orders");
        }
        return $result;
    }
}
