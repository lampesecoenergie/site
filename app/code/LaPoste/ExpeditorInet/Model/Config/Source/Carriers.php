<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module
 * to newer versions in the future.
 *
 * @copyright 2017 La Poste
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace LaPoste\ExpeditorInet\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Option\ArrayInterface;
use Magento\Shipping\Model\CarrierFactory;

/**
 * Config source for shipping carriers.
 *
 * @author Smile (http://www.smile.fr)
 */
class Carriers implements ArrayInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CarrierFactory
     */
    protected $carrierFactory;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param CarrierFactory $carrierFactory
     */
    public function __construct(ScopeConfigInterface $scopeConfig, CarrierFactory $carrierFactory)
    {
        $this->scopeConfig = $scopeConfig;
        $this->carrierFactory = $carrierFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $config = $this->scopeConfig->getValue('carriers');
            $carriers = [['value' => 'custom', 'label' => __('Custom Value')]];

            foreach (array_keys($config) as $carrierCode) {
                $carrier = $this->carrierFactory->create($carrierCode);
                if ($carrier && $carrier->isTrackingAvailable()) {
                    $carriers[] = ['value' => $carrierCode, 'label' => $carrier->getConfigData('title')];
                }
            }

            $this->options = $carriers;
        }

        return $this->options;
    }
}
