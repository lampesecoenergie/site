<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Fooman\OrderManager\Block\Adminhtml;

class Carriers extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Shipping\Model\Config
     */
    private $shippingConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Shipping\Model\Config $shippingConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Shipping\Model\Config $shippingConfig,
        array $data = []
    ) {
        $this->shippingConfig = $shippingConfig;

        parent::__construct($context, $data);
    }

    /**
     * get list of all carriers that support tracking
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'carriers' => $this->getCarriers(),
            'preselectedCarrier' => $this->getPreselectedCarrier(),
        ];
    }

    public function getCarriers()
    {
        $data = [
            ['value' => 'custom', 'text' => $this->getCustomCarrierTitle()],
        ];

        $carriers = $this->shippingConfig->getAllCarriers();

        foreach ($carriers as $code => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                $data[] = [
                    'value' => $code,
                    'text' => $carrier->getConfigData('title'),
                ];
            }
        }

        return $data;
    }

    public function getPreselectedCarrier()
    {
        return $this->_scopeConfig->getValue('ordermanager/settings/preselectedcarrier');
    }

    public function getCustomCarrierTitle()
    {
        return $this->_scopeConfig->getValue('ordermanager/settings/customtitle');
    }
}
