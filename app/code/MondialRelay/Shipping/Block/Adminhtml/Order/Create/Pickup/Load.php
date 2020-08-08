<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Block\Adminhtml\Order\Create\Pickup;

use MondialRelay\Shipping\Model\Carrier\MondialRelay;
use MondialRelay\Shipping\Model\Pickup as PickupManager;
use MondialRelay\Shipping\Helper\Data as ShippingHelper;
use MondialRelay\Shipping\Model\Pickup\Collection;
use MondialRelay\Shipping\Model\Config\Source\Calculation;
use Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Quote\Model\Quote\Address;

/**
 * Class Load
 */
class Load extends AbstractCreate
{
    /**
     * @var Collection $list
     */
    protected $list = null;

    /**
     * @var string $current
     */
    protected $current = null;

    /**
     * @var PickupManager $pickupManager
     */
    protected $pickupManager;

    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * @param Context $context
     * @param Quote $sessionQuote
     * @param Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param PickupManager $pickupManager
     * @param ShippingHelper $shippingHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Quote $sessionQuote,
        Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        PickupManager $pickupManager,
        ShippingHelper $shippingHelper,
        array $data = []
    ) {
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);

        $this->pickupManager  = $pickupManager;
        $this->shippingHelper = $shippingHelper;
    }

    /**
     * Retrieve if pickup location can be shown
     *
     * @return bool
     */
    public function canShow()
    {
        return $this->getAddress()->getShippingMethod() == $this->getPickupMethod();
    }

    /**
     * Retrieve current selected pickup
     *
     * @return string
     */
    public function getCurrent()
    {
        if ($this->current === null) {
            $current = $this->pickupManager->current($this->getQuote()->getId());
            $this->current = $current->getNum();
        }

        return $this->current;
    }

    /**
     * Load Pickup
     *
     * @return \MondialRelay\Shipping\Model\Pickup\Collection
     */
    public function getList()
    {
        if ($this->list === null) {
            $filters = [
                'weight' => $this->getWeight(),
                'size'   => $this->getSize(),
            ];
            $codes = $this->shippingHelper->filterPickupCodes($filters);
            foreach ($codes as $code => $label) {
                $this->list = $this->pickupManager->getList(
                    $this->getPostcode(),
                    $this->getCountryId(),
                    $code
                );
            }
        }

        return $this->list;
    }

    /**
     * Retrieve postcode
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->getAddress()->getPostcode();
    }

    /**
     * Retrieve total cart zize
     *
     * @return float
     */
    public function getSize()
    {
        $size = 0;
        $maxProductSize = 0;
        $length = $this->shippingHelper->getLengthAttribute();
        $width = $this->shippingHelper->getWidthAttribute();
        $height = $this->shippingHelper->getHeightAttribute();
        if ($length && $width && $height) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($this->getAddress()->getAllVisibleItems() as $item) {
                $product = $item->getProduct();
                $totalSize = $product->getData($length) + $product->getData($width) + $product->getData($height);
                $size += $totalSize * $item->getQty();
                if ($totalSize > $maxProductSize) {
                    $maxProductSize = $totalSize;
                }
            }
        }
        if ($this->shippingHelper->getCalculation('size') == Calculation::MONDIAL_RELAY_CALCULATION_PRODUCT) {
            $size = $maxProductSize;
        }

        return $size;
    }

    /**
     * Retrieve total cart Weight
     *
     * @return float
     */
    public function getWeight()
    {
        $weight = $this->getAddress()->getWeight();

        if ($this->shippingHelper->getCalculation('weight') == Calculation::MONDIAL_RELAY_CALCULATION_PRODUCT) {
            $weight = 0;
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($this->getAddress()->getAllVisibleItems() as $item) {
                if ($item->getWeight() > $weight) {
                    $weight = $item->getWeight();
                }
            }
        }

        return $weight;
    }

    /**
     * Retrieve country id
     *
     * @return string
     */
    public function getCountryId()
    {
        return $this->shippingHelper->getCountry(
            $this->getAddress()->getCountryId(),
            $this->getAddress()->getPostcode()
        );
    }

    /**
     * Retrieve Shipping Address data
     *
     * @return Address
     */
    public function getAddress()
    {
        return $this->getQuote()->getShippingAddress();
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
}
