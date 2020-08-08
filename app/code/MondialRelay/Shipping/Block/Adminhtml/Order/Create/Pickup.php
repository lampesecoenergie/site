<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Block\Adminhtml\Order\Create;

use MondialRelay\Shipping\Model\Carrier\MondialRelay;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session\Quote;

/**
 * Class Pickup
 */
class Pickup extends Template
{
    /**
     * @var Quote $sessionQuote
     */
    protected $sessionQuote;

    /**
     * @param Context $context
     * @param Quote $sessionQuote
     * @param array $data
     */
    public function __construct(
        Context $context,
        Quote $sessionQuote,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->sessionQuote = $sessionQuote;
    }

    /**
     * Retrieve Pickup Method
     *
     * @return string
     */
    public function getPickupMethod()
    {
        return MondialRelay::SHIPPING_CARRIER_PICKUP_METHOD;
    }

    /**
     * Retrieve if pickup data can be load
     *
     * @return bool
     */
    public function canLoad()
    {
        return $this->sessionQuote->getQuote()->getShippingAddress()->getShippingMethod() == $this->getPickupMethod();
    }
}
