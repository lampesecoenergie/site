<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Block\Adminhtml\Order\Shipment\Create;

use MondialRelay\Shipping\Model\Carrier\MondialRelay;
use MondialRelay\Shipping\Helper\Data as ShippingHelper;
use Magento\Shipping\Block\Adminhtml\View\Form;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Helper\Admin;
use Magento\Shipping\Model\CarrierFactory;

/**
 * Class Label
 */
class Label extends Form
{
    /**
     * @var string $_template
     * @phpcs:disable
     */
    protected $_template = 'order/new/label.phtml';

    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Admin $adminHelper
     * @param CarrierFactory $carrierFactory
     * @param ShippingHelper $shippingHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Admin $adminHelper,
        CarrierFactory $carrierFactory,
        ShippingHelper $shippingHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $carrierFactory, $data);

        $this->shippingHelper = $shippingHelper;
    }

    /**
     * Can Show Label Auto Creation
     *
     * @return bool
     */
    public function canShowAutoCreate()
    {
        $method = $this->getOrder()->getShippingMethod(true);

        $canShow = false;

        if ($method->getData('carrier_code') == MondialRelay::SHIPPING_CARRIER_CODE) {
            $canShow = true;
        }

        return $canShow;
    }

    /**
     * Retrieve store default weight unit
     *
     * @return string
     */
    public function getStoreWeightUnit()
    {
        return $this->shippingHelper->getStoreWeightUnit($this->getOrder()->getStoreId(), true);
    }
}
