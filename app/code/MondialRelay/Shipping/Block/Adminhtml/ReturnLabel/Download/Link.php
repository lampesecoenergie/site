<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Block\Adminhtml\ReturnLabel\Download;

use MondialRelay\Shipping\Helper\Data as ShippingHelper;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

/**
 * Class Link
 */
class Link extends Template
{
    /**
     * @var string $_template
     * @phpcs:disable
     */
    protected $_template = 'MondialRelay_Shipping::return/download/link.phtml';

    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * @var Registry $coreRegistry
     */
    protected $coreRegistry;

    /**
     * @param Context $context
     * @param ShippingHelper $shippingHelper
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Context $context,
        ShippingHelper $shippingHelper,
        Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->shippingHelper = $shippingHelper;
        $this->coreRegistry   = $coreRegistry;
    }

    /**
     * Retrieve download link
     *
     * @return bool
     */
    public function isFile()
    {
        $file = $this->shippingHelper->getReturnLabelPath($this->getOrder(), true);

        if (!is_file($file)) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve download URL
     *
     * @return string
     */
    public function getDownloadUrl()
    {
        return $this->getUrl('*/*/download', ['order_id' => $this->getOrder()->getId()]);
    }

    /**
     * Retrieve direct file link
     *
     * @return string
     */
    public function getDirectUrl()
    {
        return $this->shippingHelper->getReturnLabelPath($this->getOrder(), true, true);
    }

    /**
     * Retrieve current order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        /** @var \Magento\Sales\Model\Order\Address $address */
        $address = $this->coreRegistry->registry('address');

        return $address->getOrder();
    }
}
