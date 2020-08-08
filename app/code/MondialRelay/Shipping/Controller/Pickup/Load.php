<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2017 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Controller\Pickup;

use MondialRelay\Shipping\Helper\Data as ShippingHelper;
use MondialRelay\Shipping\Model\Config\Source\Calculation;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

/**
 * Class Load
 */
class Load extends Action
{
    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * @var Onepage $onepage
     */
    protected $onepage;

    /**
     * @param Context $context
     * @param ShippingHelper $shippingHelper
     * @param Onepage $onepage
     */
    public function __construct(
        Context $context,
        ShippingHelper $shippingHelper,
        Onepage $onepage
    ) {
        parent::__construct($context);

        $this->shippingHelper = $shippingHelper;
        $this->onepage        = $onepage;
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Layout\Interceptor $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);

        $address = $this->getAddress();

        $this->getRequest()->setParams($address);

        $blocks = ['mondialrelay_pickup_load'];
        if ($this->shippingHelper->isDebugMode()) {
            $blocks[] = 'mondialrelay_pickup_load_debug';
        }
        foreach ($blocks as $block) {
            /** @var \MondialRelay\Shipping\Block\Frontend\Pickup\Load $block */
            $block = $result->getLayout()->getBlock($block);
            $block->setData($address);
        }

        return $result;
    }

    /**
     * Retrieve current address
     *
     * @return array
     */
    protected function getAddress()
    {
        $shipping = $this->onepage->getQuote()->getShippingAddress();

        /* Weight */
        $weight = $shipping->getWeight();
        if ($this->shippingHelper->getCalculation('weight') == Calculation::MONDIAL_RELAY_CALCULATION_PRODUCT) {
            $weight = 0;
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($shipping->getAllVisibleItems() as $item) {
                if ($item->getWeight() > $weight) {
                    $weight = $item->getWeight();
                }
            }
        }

        /* Size */
        $size = 0;
        $maxProductSize = 0;
        $length = $this->shippingHelper->getLengthAttribute();
        $width = $this->shippingHelper->getWidthAttribute();
        $height = $this->shippingHelper->getHeightAttribute();
        if ($length && $width && $height) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($shipping->getAllItems() as $item) {
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

        if (!$shipping->getPostcode() && $this->onepage->getCustomerSession()->isLoggedIn()) {
            $default = $this->onepage->getCustomerSession()->getCustomer()->getDefaultShippingAddress();
            if ($default) {
                $shipping = $default;
            }
        }

        $address = $this->shippingHelper->getDefaultAddress();

        if ($shipping->getPostcode()) {
            $countryId = $this->shippingHelper->getCountry($shipping->getCountryId());

            $address = [
                'postcode'   => $shipping->getPostcode(),
                'country_id' => $countryId,
            ];
        }

        $filters = [
            'weight' => $weight,
            'size'   => $size,
        ];
        $address['code'] = key($this->shippingHelper->filterPickupCodes($filters));

        $data = $this->getRequest()->getParams();

        if (!empty($data)) {
            $address = [
                'postcode'   => isset($data['postcode'])   ? $data['postcode']   : $address['postcode'],
                'country_id' => isset($data['country_id']) ? $data['country_id'] : $address['country_id'],
                'code'       => isset($data['code'])       ? $data['code']       : $address['code'],
            ];
        }

        $address['weight'] = $weight;
        $address['size']   = $size;

        return $address;
    }
}
