<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Observer;

use MondialRelay\Shipping\Model\Pickup;
use MondialRelay\Shipping\Api\Data\ShippingDataInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Class AddDataToQuoteAddressObserver
 */
class AddDataToQuoteAddressObserver implements ObserverInterface
{
    /**
     * @var Pickup $pickup
     */
    protected $pickup;

    /**
     * @param Pickup $pickup
     */
    public function __construct(
        Pickup $pickup
    ) {
        $this->pickup = $pickup;
    }

    /**
     * Add pickup data to quote address
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        $shippingAddress = $order->getShippingAddress();

        if (!$shippingAddress) {
            return $this;
        }

        if (!$shippingAddress->getId()) {
            return $this;
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        $pickupId = $shippingAddress->getData(ShippingDataInterface::MONDIAL_RELAY_PICKUP_ID);
        $code     = $shippingAddress->getData(ShippingDataInterface::MONDIAL_RELAY_CODE);

        if ($pickupId) {
            $this->pickup->save(
                $quote->getId(),
                $pickupId,
                $shippingAddress->getCountryId(),
                $code
            );
            $quote->getShippingAddress()->setSameAsBilling(1);
        }

        return $this;
    }
}
