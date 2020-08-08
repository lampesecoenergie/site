<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Observer\Backend;

use MondialRelay\Shipping\Model\PickupFactory;
use MondialRelay\Shipping\Model\Carrier\MondialRelay;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Class AddPickupDataToQuoteObserver
 */
class AddPickupDataToQuoteObserver implements ObserverInterface
{
    /**
     * @var PickupFactory $pickupFactory
     */
    protected $pickupFactory;

    /**
     * @param PickupFactory $pickupFactory
     */
    public function __construct(
        PickupFactory $pickupFactory
    ) {
        $this->pickupFactory = $pickupFactory;
    }

    /**
     * Add data to order address
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\AdminOrder\Create $orderCreateModel */
        $orderCreateModel = $observer->getEvent()->getOrderCreateModel();

        /** @var array $request */
        $request = $observer->getEvent()->getRequest();

        $shippingMethod = $orderCreateModel->getShippingAddress()->getShippingMethod();

        $quoteId = $orderCreateModel->getQuote()->getId();

        $pickup = $this->pickupFactory->create();

        if ($shippingMethod !== MondialRelay::SHIPPING_CARRIER_PICKUP_METHOD) {
            $pickup->reset($quoteId);
        }

        if (isset($request['mondialrelay']['pickup'])) {
            if ($request['mondialrelay']['pickup']) {
                list($pickupId, $countryId, $code) = explode('-', $request['mondialrelay']['pickup']);
                $pickup->save($quoteId, $pickupId, $countryId, $code);
            }
        }

        return $this;
    }
}
