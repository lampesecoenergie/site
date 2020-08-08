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
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Order;

use Ced\Amazon\Api\Data\Order\Import\ResultInterface;

class Import extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    public $resultRedirectFactory;
    /**
     * @var \Ced\Amazon\Helper\Order
     */
    public $orderHelper;

    /**
     * Fetch constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Ced\Amazon\Helper\Order $orderHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Ced\Amazon\Helper\Order $orderHelper
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->orderHelper = $orderHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $orderId = trim((string)$this->getRequest()->getParam('order_id', ''));
        $status = $this->getRequest()->getParam('status', []);
        $status = is_array($status) ? $status : [];
        $orderDate = trim((string)$this->getRequest()->getParam('created_after', ''));
        $buyerEmail = trim((string)$this->getRequest()->getParam('buyer_email', ''));
        $accountIds = $this->getRequest()->getParam('account_id', []);

        $limit = $this->getRequest()->getParam('limit', 10);
        if ($limit > 10 || $limit < 1) {
            $limit = 10;
        }

        $page = false;

        $status = $this->orderHelper->import($accountIds, $orderId, $buyerEmail, $status, $orderDate, $limit, $page);

        if ($status) {
            $this->messageManager->addSuccessMessage((string)$status . ' New orders imported from Amazon.');
        } else {
            $this->messageManager->addNoticeMessage('No new orders are imported. Kindly check failed orders.');
        }

        $result = $this->resultRedirectFactory->create();
        $result->setPath('amazon/order/index');
        return $result;
    }
}
