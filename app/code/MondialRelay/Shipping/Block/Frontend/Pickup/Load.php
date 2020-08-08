<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Block\Frontend\Pickup;

use MondialRelay\Shipping\Model\Pickup;
use MondialRelay\Shipping\Helper\Data as ShippingHelper;
use MondialRelay\Shipping\Model\Pickup\Collection;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Load
 */
class Load extends Template
{
    /**
     * @var Collection $list
     */
    protected $list;

    /**
     * @var Pickup $pickupManager
     */
    protected $pickupManager;

    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * @var CollectionFactory $countryFactory
     */
    protected $countryFactory;

    /**
     * @param Context $context
     * @param Pickup $pickupManager
     * @param ShippingHelper $shippingHelper
     * @param CollectionFactory $countryFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Pickup $pickupManager,
        ShippingHelper $shippingHelper,
        CollectionFactory $countryFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->countryFactory = $countryFactory;
        $this->pickupManager  = $pickupManager;
        $this->shippingHelper = $shippingHelper;
    }

    /**
     * Load Pickup
     *
     * @return \MondialRelay\Shipping\Model\Pickup\Collection
     */
    public function getList()
    {
        if (is_null($this->list)) {
            $this->list = $this->pickupManager->getList(
                $this->getPostcode(),
                $this->getCountryId(),
                $this->getCode()
            );
        }

        return $this->list;
    }

    /**
     * Retrieve if all form field are empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return !$this->getPostcode();
    }

    /**
     * Retrieve Pickup as Json
     *
     * @return string
     */
    public function getJson()
    {
        $collection = $this->getList();

        $list = [];

        /** @var \MondialRelay\Shipping\Model\Pickup $item */
        foreach ($collection as $item) {
            $list[] = [
                '<strong>' . $item->getLgadr1() . '</strong><br />' . $item->getLgadr3(),
                $item->getLatitude(),
                $item->getLongitude(),
                'mr-pickup-' . $item->getNum(),
                $this->getViewFileUrl('MondialRelay_Shipping::images/icons/mondialrelay.png'),
            ];
        }

        return json_encode($list);
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
     * Retrieve Countries
     *
     * @return array
     */
    public function getCountries()
    {
        $config = $this->_scopeConfig->getValue(
            ShippingHelper::CONFIG_PREFIX . 'pickup/specificcountry',
            ScopeInterface::SCOPE_STORE
        );

        $ids = [];

        if ($config) {
            $ids = explode(',', $config);
        }

        if (empty($ids)) {
            $ids[] = $this->getCountryId();
        }

        /** @var \Magento\Directory\Model\ResourceModel\Country\Collection $collection */
        $collection = $this->countryFactory->create();

        return $collection->addCountryIdFilter($ids)->toOptionArray(false);
    }

    /**
     * Retrieve Full Street
     *
     * @param Pickup $pickup
     * @param string $separator
     * @return string
     */
    public function getFullStreet($pickup, $separator)
    {
        $street = array_filter(
            [$pickup->getLgadr2(), $pickup->getLgadr3(), $pickup->getLgadr4()]
        );

        return join($separator, $street);
    }

    /**
     * Retrieve available codes
     *
     * @return array
     */
    public function getCodes()
    {
        $filters = [
            'weight' => $this->getWeight(),
            'size'   => $this->getSize(),
        ];
        return $this->shippingHelper->filterPickupCodes($filters);
    }

    /**
     * Retrieve postcode
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getValue('code');
    }

    /**
     * Retrieve postcode
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->getValue('postcode');
    }

    /**
     * Retrieve weight
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->getValue('weight');
    }

    /**
     * Retrieve size
     *
     * @return float
     */
    public function getSize()
    {
        return $this->getValue('size');
    }

    /**
     * Retrieve country id
     *
     * @return int
     */
    public function getCountryId()
    {
        return $this->shippingHelper->getCountry($this->getValue('country_id'), $this->getPostcode());
    }

    /**
     * Retrieve value
     *
     * @param $code
     *
     * @return string
     */
    protected function getValue($code)
    {
        /** @var string $value */
        $value  = $this->getData($code);
        $params = $this->getRequest()->getParams();

        if (!$value && isset($params[$code])) {
            $value = $params[$code];
        }

        return $value;
    }

    /**
     * Postcode setter
     *
     * @param string $postcode
     */
    public function setPostcode($postcode)
    {
        $this->setData('postcode', $postcode);
    }

    /**
     * Country setter
     *
     * @param string $countryId
     */
    public function setCountryId($countryId)
    {
        $this->setData('country_id', $countryId);
    }

    /**
     * Retrieve if debug mode is active
     *
     * @return bool
     */
    public function isDebug()
    {
        return $this->shippingHelper->isDebugMode();
    }
}
