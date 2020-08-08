<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Model;

use MondialRelay\Shipping\Api\Data\ShippingDataInterface;
use MondialRelay\Shipping\Helper\Data as ShippingHelper;
use MondialRelay\Shipping\Model\Carrier\MondialRelay;
use Magento\Framework\DataObject;

/**
 * Class Address
 */
class Address extends DataObject
{
    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * @var Pickup $pickup
     */
    protected $pickup;

    /**
     * @param ShippingHelper $shippingHelper
     * @param Pickup $pickup
     * @param array $data
     */
    public function __construct(
        ShippingHelper $shippingHelper,
        Pickup $pickup,
        array $data = []
    ) {
        parent::__construct($data);

        $this->pickup         = $pickup;
        $this->shippingHelper = $shippingHelper;
    }

    /**
     * Update Shipping Address
     *
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     */
    public function updateShippingAddress($order, $quote)
    {
        if (!$order) {
            return $this;
        }

        if (!$quote) {
            return $this;
        }

        $address = $order->getShippingAddress();

        if (!$address) {
            return $this;
        }

        if ($address->getAddressType() !== 'shipping') {
            return $this;
        }

        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();

        if (!$shippingMethod) {
            return $this;
        }

        list($code, $method) = explode('_', $shippingMethod);

        /* Reset Data */
        $address->setMondialrelayCode(null);
        $address->setMondialrelayPickupId(null);

        if ($code !== MondialRelay::SHIPPING_CARRIER_CODE) {
            return $this;
        }

        if ($this->shippingHelper->getPackagingWeight()) {
            $order->setData(
                ShippingDataInterface::MONDIAL_RELAY_PACKAGING_WEIGHT,
                (float) $this->shippingHelper->getPackagingWeight()
            );
        }

        /* Set code */
        $code = $this->shippingHelper->getConfig($method . '/code');
        if ($code) {
            $address->setMondialrelayCode($code);
        }

        /* Set pickup data */
        $pickup = $this->pickup->getPickupAddress($quote->getId());

        if (is_array($pickup)) {
            $address->setCompany($pickup['company'])
                ->setStreet([$pickup['street']])
                ->setPostcode($pickup['postcode'])
                ->setCity($pickup['city'])
                ->setCountryId($pickup['country_id'])
                ->setFax('')
                ->setCustomerAddressId(null)
                ->setMondialrelayPickupId($pickup['pickup_id'])
                ->setMondialrelayCode($pickup['code'])
                ->setSameAsBilling(0)
                ->setSaveInAddressBook(0);

            $region = $this->shippingHelper->getRegion($pickup['country_id'], $pickup['postcode']);
            if ($region->hasData()) {
                $address->setRegion($region->getDefaultName())
                    ->setRegionId($region->getRegionId())
                    ->setRegionCode($region->getCode());
            }

            $this->pickup->reset($quote->getId());
        }

        return $this;
    }
}
