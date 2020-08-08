<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Model\Config\Source;

use MondialRelay\Shipping\Model\Config\Source\Code;
use MondialRelay\Shipping\Model\Pickup as PickupModel;
use MondialRelay\Shipping\Helper\Data as ShippingHelper;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Pickup
 */
class Pickup implements ArrayInterface
{
    /**
     * @var PickupModel $pickup
     */
    protected $pickup;

    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * @var Code $code
     */
    protected $code;

    /**
     * @param PickupModel $pickup
     * @param ShippingHelper $shippingHelper
     * @param Code $code
     */
    public function __construct(
        PickupModel $pickup,
        ShippingHelper $shippingHelper,
        Code $code
    ) {
        $this->pickup         = $pickup;
        $this->shippingHelper = $shippingHelper;
        $this->code           = $code;
    }

    /**
     * Get options as array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => '',
                'label' => __('Select'),
            ]
        ];

        $pickup = [];
        foreach ($this->code->toArray() as $code => $label) {
            $pickup = $this->pickup->getList(
                $this->shippingHelper->getRecipientReturnConfig('postcode'),
                $this->shippingHelper->getRecipientReturnConfig('country'),
                $code
            );
        }

        /** @var PickupModel $item */
        foreach ($pickup as $item) {
            $address = $item->getCode() . ' - ' .
                trim($item->getLgadr1()) . ' - ' .
                trim($item->getLgadr3()) . ' ' .
                $item->getCp() . ' ' .
                $item->getVille();

            $options[] = [
                'value' => $item->getNum() . '-' . $item->getCode(),
                'label' => $address,
            ];
        }

        return $options;
    }
}
