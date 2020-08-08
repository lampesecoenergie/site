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

class Sync extends \Magento\Backend\App\Action
{

    public $orderInterface;

    public $helperOrder;

    public $resultPageFactory;
    /**
     * Index constructor.
     *
     * @param \Magento\Backend\App\Action\Context        $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderInterface,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ced\Cdiscount\Helper\Order $helperOrder
    ) {
        parent::__construct($context);
        $this->orderInterface = $orderInterface;
        $this->helperOrder = $helperOrder;
        $this->resultPageFactory = $resultPageFactory;
    }
    /**
     * Jet Product Detail Page
     *
     * @return String
     */

    public function execute()
    {
        $magentoOrderId = $this->getRequest()->getParam('id');
        $cdiscountOrderId = $this->getRequest()->getParam('cd_oid');
        $trackArray = [];
        $data = [];
        $tracksCollection = $this->orderInterface->get($magentoOrderId)->getTracksCollection()->getItems();

        foreach ($tracksCollection as $track) {
            $trackArray = $track->getData();
        }

        if ($this->orderInterface->get($magentoOrderId)->getStatus() == 'complete') {
            if (isset($trackArray['track_number']) && !empty($cdiscountOrderId)) {
                $data['OrderId'] = $magentoOrderId;
                $data['CdiscountOrderID'] = $this->getRequest()->getParam('cd_oid');
                $data['ShippingProvider'] = $trackArray['title'];
                $data['TrackingNumber'] = $trackArray['track_number'];
            }
        }

        if (isset($data) && !empty($data)) {
            $this->helperOrder->shipOrder($data, true);
        }
        return $this->_redirect('cdiscount/order/index');
    }
}
