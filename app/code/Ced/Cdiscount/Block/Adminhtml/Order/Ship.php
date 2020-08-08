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

namespace Ced\Cdiscount\Block\Adminhtml\Order;

use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use \Magento\Sales\Helper\Admin;
use Magento\Framework\ObjectManagerInterface;
use Ced\Cdiscount\Model\Orders;

/**
 * Class Ship
 *
 * @package Ced\Cdiscount\Block\Adminhtml\Order
 */
class Ship extends AbstractOrder implements TabInterface
{

    public $_template = 'order/ship.phtml';

    /**
     * @var ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var Orders
     */
    public $order;

    /**
     * Ship constructor.
     *
     * @param Context                $context
     * @param Registry               $registry
     * @param Admin                  $adminHelper
     * @param ObjectManagerInterface $objectManager
     * @param Orders                 $orders
     * @param array                  $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Admin $adminHelper,
        ObjectManagerInterface $objectManager,
        Orders $orders,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->objectManager = $objectManager;
        $this->order = $orders;
    }

    /**
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->objectManager;
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

    public function getHelper($helper)
    {
        $helper = $this->objectManager->get("Ced\Cdiscount\Helper" . $helper);
        return $helper;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getModel()
    {
        $orderId = $this->getOrder()->getId();
        $CdiscountOrder = $this->getObjectManager()->get('Ced\Cdiscount\Model\Orders')
            ->getCollection()->addFieldToFilter('magento_order_id', $orderId)
            ->getFirstItem();
        return $CdiscountOrder;
    }

    /**
     * @param $resultdata
     */
    public function setOrderResult($resultdata)
    {
        return $this->_coreRegistry->register('current_cdiscount_order', $resultdata);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Cdiscount');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Cdiscount');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        $data = $this->getModel();
        if (!empty($data->getData())) {
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
}
