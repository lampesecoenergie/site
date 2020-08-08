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

namespace Ced\Amazon\Block\Adminhtml\Order;

use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Helper\Admin;
use Ced\Amazon\Model\Source\Shipment\Status;
use Ced\Amazon\Repository\Order as OrderRepository;
use Ced\Amazon\Helper\Order as OrderHelper;
use Ced\Amazon\Model\ResourceModel\Order\Item\CollectionFactory as ItemCollectionFactory;

/**
 * Class Shipment
 * @package Ced\Amazon\Block\Adminhtml\Order
 */
class Shipment extends AbstractOrder implements TabInterface
{
    /** @var OrderRepository  */
    public $orderRepository;

    /** @var OrderHelper  */
    public $helper;

    /** @var Status  */
    public $options;

    /** @var ItemCollectionFactory  */
    public $orderItemsCollectionFactory;

    /**
     * Shipment constructor.
     * @param OrderRepository $orderRepository
     * @param OrderHelper $helper
     * @param Status $options
     * @param Context $context
     * @param Registry $registry
     * @param Admin $adminHelper
     * @param ItemCollectionFactory $orderItemsCollectionFactory
     * @param array $data
     */
    public function __construct(
        OrderRepository $orderRepository,
        OrderHelper $helper,
        Status $options,
        Context $context,
        Registry $registry,
        Admin $adminHelper,
        ItemCollectionFactory $orderItemsCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->orderRepository = $orderRepository;
        $this->helper = $helper;
        $this->options = $options;
        $this->orderItemsCollectionFactory = $orderItemsCollectionFactory;
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    public function getHelper()
    {
        return $this->helper;
    }

    public function getModel()
    {
        $orderId = $this->getOrder()->getId();
        /** @var \Ced\Amazon\Model\Order|null $marketplaceOrder */
        try {
            $marketplaceOrder = $this->orderRepository->getByOrderId($orderId);
        } catch (\Exception $e) {
            $marketplaceOrder = null;
        }

        return $marketplaceOrder;
    }

    public function getFulfillments()
    {
        $fulfillments = [];
        $mpOrder = $this->getModel();
        if (!empty($mpOrder)) {
            $fulfillments = json_decode($mpOrder->getFulfillments(), true);
            $fulfillments = (isset($fulfillments) && is_array($fulfillments)) ? $fulfillments : [];
        }

        return $fulfillments;
    }

    public function getAdjustments()
    {
        $adjustments = [];
        $mpOrder = $this->getModel();
        if (!empty($mpOrder)) {
            $adjustments = json_decode($mpOrder->getAdjustments(), true);
            $adjustments = (isset($adjustments) && is_array($adjustments)) ? $adjustments : [];
        }

        return $adjustments;
    }

    public function getOrderData()
    {
        $mpOrderData = [];
        $mpOrder = $this->getModel();
        if (!empty($mpOrder)) {
            $mpOrderData = json_decode($mpOrder->getOrderData(), true);
            $mpOrderData = (isset($mpOrderData) && is_array($mpOrderData)) ? $mpOrderData : [];
        }

        return $mpOrderData;
    }

    /**
     * Load order item data from json
     * @return array|mixed
     * @deprecated: Use items table
     */
    public function getOrderItems()
    {
        $orderItems = [];
        $mpOrder = $this->getModel();
        if (!empty($mpOrder)) {
            $orderItems = json_decode($mpOrder->getOrderItems(), true);
            $orderItems = (isset($orderItems) && is_array($orderItems)) ? $orderItems : [];
        }
        return $orderItems;
    }

    public function processOrderItems()
    {
        return $this->helper->processOrderItems(
            $this->getOrderItems(),
            $this->getFulfillments(),
            $this->getAdjustments()
        );
    }

    /**
     * @param $resultdata
     */
    public function setOrderResult($resultdata)
    {
        return $this->_coreRegistry->register('current_amazon_order', $resultdata);
    }

    /**
     * Get status html
     * @param $status
     * @return string
     */
    public function getStatusText($status)
    {
        if (empty($status)) {
            $status = \Ced\Amazon\Model\Source\Shipment\Status::NOT_SUBMITTED;
        }

        $label = $this->options->getOptionText($status);

        $html = "<span class='grid-severity-minor'>{$label}</span>";
        if ($status ==  \Ced\Amazon\Model\Source\Shipment\Status::DONE) {
            $html = "<span class='grid-severity-notice'>{$label}</span>";
        } elseif ($status !== \Ced\Amazon\Model\Source\Shipment\Status::SUBMITTED) {
            $html = "<span class='grid-severity-critical'>{$label}</span>";
        }

        return $html;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Amazon');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Amazon');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        $order = $this->getModel();
        if (isset($order) && $order instanceof \Ced\Amazon\Api\Data\OrderInterface) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        $data = $this->getModel();
        if (!empty($data->getData())) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get All Amazon Order Items
     * @return \Ced\Amazon\Api\Data\Order\ItemInterface[]
     */
    public function getAmazonOrderItems()
    {
        $amazonOrderId = $this->getModel()->getAmazonOrderId();
        /** @var \Ced\Amazon\Model\ResourceModel\Order\Item\Collection $orderItemsCollection */
        $orderItemsCollection = $this->orderItemsCollectionFactory->create();
        $orderItems = $orderItemsCollection->addFieldToFilter('order_id', ['eq' => $amazonOrderId])
            ->getItems();
        return $orderItems;
    }
}
