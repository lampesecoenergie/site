<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2017 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Block\Adminhtml\Order\View;

use MondialRelay\Shipping\Api\Data\ShippingDataInterface;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

/**
 * Class Shipping
 */
class Shipping extends Template implements TabInterface
{
    /**
     * @var string $_template
     * @phpcs:disable
     */
    protected $_template = 'order/view/shipping.phtml';

    /**
     * @var Registry $coreRegistry
     */
    protected $coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->coreRegistry = $registry;
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * Retrieve Shipping Address
     *
     * @return \Magento\Sales\Model\Order\Address|null
     */
    public function getShippingAddress()
    {
        return $this->getOrder()->getShippingAddress();
    }

    /**
     * Retrieve product code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getShippingAddress()->getData(ShippingDataInterface::MONDIAL_RELAY_CODE);
    }

    /**
     * Retrieve pickup Id
     *
     * @return string
     */
    public function getPickupId()
    {
        return $this->getShippingAddress()->getData(ShippingDataInterface::MONDIAL_RELAY_PICKUP_ID);
    }

    /**
     * Retrieve Network Code
     *
     * @return string
     */
    public function getCountryId()
    {
        return $this->getShippingAddress()->getCountryId();
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Mondial Relay');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Mondial Relay');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        $shippingAddress = $this->getShippingAddress();

        if (!$shippingAddress) {
            return false;
        }

        if (!$shippingAddress->getData(ShippingDataInterface::MONDIAL_RELAY_CODE)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
