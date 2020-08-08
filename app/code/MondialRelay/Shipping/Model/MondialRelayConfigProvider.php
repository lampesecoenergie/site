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

use MondialRelay\Shipping\Helper\Data as ShippingHelper;
use MondialRelay\Shipping\Model\Carrier\MondialRelay;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\Data\AddressExtensionFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Class MondialRelayConfigProvider
 */
class MondialRelayConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * @var Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var Address $address
     */
    protected $address;

    /**
     * @var AddressExtensionFactory $addressExtensionFactory
     */
    protected $addressExtensionFactory;

    /**
     * @param Address $address
     * @param AddressExtensionFactory $addressExtensionFactory
     * @param Session $checkoutSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Address $address,
        AddressExtensionFactory $addressExtensionFactory,
        Session $checkoutSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->address                 = $address;
        $this->checkoutSession         = $checkoutSession;
        $this->scopeConfig             = $scopeConfig;
        $this->storeManager            = $storeManager;
        $this->addressExtensionFactory = $addressExtensionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();

        $output = [
            'mondialrelayUrl'     => $store->getUrl('mondialrelay'),
            'mondialrelayPickup'  => MondialRelay::SHIPPING_CARRIER_PICKUP_METHOD,
            'mondialrelayMapType' => $this->scopeConfig->getValue(
                ShippingHelper::CONFIG_PREFIX . 'pickup/map_type',
                ScopeInterface::SCOPE_STORE
            ),
            'mondialrelayOpen'    => $this->scopeConfig->getValue(
                ShippingHelper::CONFIG_PREFIX . 'pickup/open',
                ScopeInterface::SCOPE_STORE
            ),
        ];

        return $output;
    }
}
