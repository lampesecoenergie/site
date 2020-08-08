<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Fooman\OrderManager\Model\Source;

use Fooman\OrderManager\Model\CarrierTitleLookup;
use Magento\Shipping\Model\Config;

class CarrierOptions
{
    /**
     * @var Config
     */
    private $shippingConfig;

    /**
     * @var CarrierTitleLookup
     */
    private $carrierTitleLookup;

    /**
     * @param Config                                        $shippingConfig
     * @param CarrierTitleLookup $carrierTitleLookup
     */
    public function __construct(
        Config $shippingConfig,
        CarrierTitleLookup $carrierTitleLookup
    ) {
        $this->shippingConfig = $shippingConfig;
        $this->carrierTitleLookup = $carrierTitleLookup;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $carriers = $this->shippingConfig->getAllCarriers();
        $data = [[
            'value' => 'custom',
            'label' => $this->carrierTitleLookup->getTitleFromCarrierCode('custom'),
        ]];

        foreach ($carriers as $code => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                $data[] = [
                    'value' => $code,
                    'label' => $carrier->getConfigData('title'),
                ];
            }
        }

        return $data;
    }
}
