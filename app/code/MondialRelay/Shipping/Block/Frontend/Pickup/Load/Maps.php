<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Block\Frontend\Pickup\Load;

use MondialRelay\Shipping\Helper\Data as ShippingHelper;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Maps
 */
class Maps extends Template
{
    /**
     * @return void
     * @phpcs:disable
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('MondialRelay_Shipping::pickup/load/maps/' . $this->getMapType() . '.phtml');
    }

    /**
     * Retrieve map type
     *
     * @return string
     */
    public function getMapType()
    {
        return $this->_scopeConfig->getValue(
            ShippingHelper::CONFIG_PREFIX . 'pickup/map_type',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve Maps API key
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->_scopeConfig->getValue(
            ShippingHelper::CONFIG_PREFIX . 'pickup/map_api_key',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if map can be loaded
     *
     * @return mixed
     */
    public function canShow()
    {
        return $this->_scopeConfig->isSetFlag(
            ShippingHelper::CONFIG_PREFIX . 'pickup/active',
            ScopeInterface::SCOPE_STORE
        );
    }
}
