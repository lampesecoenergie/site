<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2017 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Observer;

use MondialRelay\Shipping\Model\Address;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Class AddDataToOrderAddressObserver
 */
class AddDataToOrderAddressObserver implements ObserverInterface
{
    /**
     * @var Address $address
     */
    protected $address;

    /**
     * @param Address $address
     */
    public function __construct(
        Address $address
    ) {
        $this->address = $address;
    }

    /**
     * Add data to order address
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        $this->address->updateShippingAddress($order, $quote);

        return $this;
    }
}
