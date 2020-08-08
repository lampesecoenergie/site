<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2017 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Model;

use Magento\Framework\Exception\LocalizedException;
use MondialRelay\Shipping\Api\Data\PickupInterface;
use MondialRelay\Shipping\Model\Pickup\Collection;
use MondialRelay\Shipping\Model\ResourceModel\Pickup as ResourceModel;
use MondialRelay\Shipping\Helper\Data as ShippingHelper;
use Magento\Framework\DataObject;

/**
 * Class Pickup
 */
class Pickup extends DataObject implements PickupInterface
{
    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * @var Collection $pickupCollection
     */
    protected $pickupCollection;

    /**
     * @var Soap $soap
     */
    protected $soap;

    /**
     * @var ResourceModel $pickup
     */
    protected $pickup;

    /**
     * @var ShippingData $shippingData
     */
    protected $shippingData;

    /**
     * @param Collection $pickupCollection
     * @param ShippingHelper $shippingHelper
     * @param Soap $soap
     * @param ResourceModel $pickup
     * @param ShippingData $shippingData
     * @param array $data
     */
    public function __construct(
        Collection $pickupCollection,
        ShippingHelper $shippingHelper,
        Soap $soap,
        ResourceModel $pickup,
        ShippingData $shippingData,
        array $data = []
    ) {
        parent::__construct($data);

        $this->shippingHelper   = $shippingHelper;
        $this->pickupCollection = $pickupCollection;
        $this->soap             = $soap;
        $this->pickup           = $pickup;
        $this->shippingData     = $shippingData;
    }

    /**
     * Retrieve Pickup List
     *
     * @param string $postcode
     * @param string $country
     * @param string $code
     * @return \MondialRelay\Shipping\Model\Pickup\Collection
     */
    public function getList($postcode, $country, $code)
    {
        return $this->pickupCollection->loadItems($this, $postcode, $country, $code);
    }

    /**
     * Load specific pickup
     *
     * @param string $pickupId
     * @param string $countryId
     * @param string $code
     * @return \MondialRelay\Shipping\Api\Data\PickupInterface
     */
    public function load($pickupId, $countryId = 'FR', $code = null)
    {
        if (!$pickupId) {
            return $this;
        }

        $data = [
            'NumPointRelais' => $pickupId,
            'Pays'           => $countryId,
        ];

        $response = $this->soap->execute('WSI4_PointRelais_Recherche', $data);

        if ($response['error']) {
            return $this;
        }

        if (isset($response['response']->PointsRelais->PointRelais_Details)) {
            $data = $response['response']->PointsRelais->PointRelais_Details;
            $this->setCode($code);
            foreach ($data as $k => $v) {
                $key = preg_replace_callback(
                    '/([A-Z])/',
                    function ($m) {
                        return "_" . strtolower($m[1]);
                    },
                    preg_replace("/_/", "", ucwords(strtolower($k), '_'))
                );
                $this->setData(trim($key, '_'), $v);
            }
        }

        return $this;
    }

    /**
     * Retrieve current pickup for quote
     *
     * @param string|int $cartId
     *
     * @return $this
     * @throws LocalizedException
     */
    public function current($cartId)
    {
        $pickup = $this->getPickupAddress($cartId);

        if (is_array($pickup)) {
            $this->load($pickup['pickup_id'], $pickup['country_id'], $pickup['code']);
        }

        return $this;
    }

    /**
     * Retrieve current Pickup Address
     *
     * @param string|int $cartId
     *
     * @return string[]|false
     * @throws LocalizedException
     */
    public function getPickupAddress($cartId)
    {
        return $this->pickup->currentPickup($cartId);
    }

    /**
     * Save pickup data for quote
     *
     * @param string $cartId
     * @param string $pickupId
     * @param string $countryId
     * @param string $code
     *
     * @return bool
     * @throws LocalizedException
     */
    public function save($cartId, $pickupId, $countryId, $code)
    {
        $this->load($pickupId, $countryId, $code);

        if (!$this->getNum()) {
            return false;
        }

        $street = array_filter(
            [trim($this->getLgadr2()), trim($this->getLgadr3()), trim($this->getLgadr4())]
        );

        $address = [
            'company'  => trim($this->getLgadr1()),
            'street'   => join("\n", $street),
            'postcode' => trim($this->getCp()),
            'city'     => trim($this->getVille())
        ];

        return $this->pickup->savePickup($cartId, $pickupId, $countryId, $code, $address);
    }

    /**
     * Reset pickup data for quote
     *
     * @param string $cartId
     *
     * @return bool
     * @throws LocalizedException
     */
    public function reset($cartId)
    {
        return $this->pickup->resetPickup($cartId);
    }

    /**
     * Retrieve shipping data for order
     *
     * @param int $orderId
     * @return ShippingData
     */
    public function shippingData($orderId)
    {
        $shippingData = $this->shippingData;
        $data = $this->pickup->shippingData($orderId);

        $shippingData->setData($data);

        return $shippingData;
    }

    /**
     * Pickup code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getData('code');
    }

    /**
     * Pickup code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->setData('code', $code);

        return $this;
    }

    /**
     * Pickup stat
     *
     * @return string
     */
    public function getStat()
    {
        return $this->getData('stat');
    }

    /**
     * Pickup identifier
     *
     * @return string
     */
    public function getNum()
    {
        return $this->getData('num');
    }

    /**
     * Pickup name line 1
     *
     * @return string
     */
    public function getLgadr1()
    {
        return $this->getData('lgadr1');
    }

    /**
     * Pickup name line 2
     *
     * @return string
     */
    public function getLgadr2()
    {
        return $this->getData('lgadr2');
    }

    /**
     * Pickup name line 3
     *
     * @return string
     */
    public function getLgadr3()
    {
        return $this->getData('lgadr3');
    }

    /**
     * Pickup name line 4
     *
     * @return string
     */
    public function getLgadr4()
    {
        return $this->getData('lgadr4');
    }

    /**
     * Pickup postcode
     *
     * @return string
     */
    public function getCp()
    {
        return $this->getData('cp');
    }

    /**
     * Pickup city
     *
     * @return string
     */
    public function getVille()
    {
        return $this->getData('ville');
    }

    /**
     * Pickup country
     *
     * @return string
     */
    public function getPays()
    {
        return $this->getData('pays');
    }

    /**
     * Pickup additional information 1
     *
     * @return string
     */
    public function getLocalisation1()
    {
        return $this->getData('localisation1');
    }

    /**
     * Pickup additional information 2
     *
     * @return string
     */
    public function getLocalisation2()
    {
        return $this->getData('localisation2');
    }

    /**
     * Pickup latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return preg_replace('/,/', '.', $this->getData('latitude'));
    }

    /**
     * Pickup longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return preg_replace('/,/', '.', $this->getData('longitude'));
    }

    /**
     * Pickup activity code
     *
     * @return string
     */
    public function getTypeactivite()
    {
        return $this->getData('typeactivite');
    }

    /**
     * Pickup nace
     *
     * @return string
     */
    public function getNace()
    {
        return $this->getData('nace');
    }

    /**
     * Pickup Information
     *
     * @return string
     */
    public function getInformation()
    {
        return $this->getData('information');
    }

    /**
     * Pickup Monday opening
     *
     * @return string|null
     */
    public function getHorairesLundi()
    {
        return $this->formatOpening(
            $this->getData('horaires_lundi')
        );
    }

    /**
     * Pickup Tuesday opening
     *
     * @return string|null
     */
    public function getHorairesMardi()
    {
        return $this->formatOpening(
            $this->getData('horaires_mardi')
        );
    }

    /**
     * Pickup Wednesday opening
     *
     * @return string|null
     */
    public function getHorairesMercredi()
    {
        return $this->formatOpening(
            $this->getData('horaires_mercredi')
        );
    }

    /**
     * Pickup Thursday opening
     *
     * @return string|null
     */
    public function getHorairesJeudi()
    {
        return $this->formatOpening(
            $this->getData('horaires_jeudi')
        );
    }

    /**
     * Pickup Friday opening
     *
     * @return string|null
     */
    public function getHorairesVendredi()
    {
        return $this->formatOpening(
            $this->getData('horaires_vendredi')
        );
    }

    /**
     * Pickup Saturday opening
     *
     * @return string|null
     */
    public function getHorairesSamedi()
    {
        return $this->formatOpening(
            $this->getData('horaires_samedi')
        );
    }

    /**
     * Pickup Sunday opening
     *
     * @return string|null
     */
    public function getHorairesDimanche()
    {
        return $this->formatOpening(
            $this->getData('horaires_dimanche')
        );
    }

    /**
     * Pickup holiday information
     *
     * @return string[]
     */
    public function getInformationsDispo()
    {
        $info = [];

        if (is_array($this->getData('informations_dispo'))) {
            foreach ($this->getData('informations_dispo') as $value) {
                $info[] = $value;
            }
        }

        return $info;
    }

    /**
     * Pickup holiday start
     *
     * @return string
     */
    public function getDebut()
    {
        return $this->getData('debut');
    }

    /**
     * Pickup holiday end
     *
     * @return string
     */
    public function getFin()
    {
        return $this->getData('fin');
    }

    /**
     * Pickup picture URL
     *
     * @return string
     */
    public function getUrlPhoto()
    {
        return $this->getData('url_photo');
    }

    /**
     * Pickup map URL
     *
     * @return string
     */
    public function getUrlPlan()
    {
        return $this->getData('url_plan');
    }

    /**
     * Pickup distance in meters
     *
     * @return string
     */
    public function getDistance()
    {
        return $this->getData('distance');
    }

    /**
     * Format opening day
     *
     * @param object $opening
     * @return string|null
     */
    protected function formatOpening($opening)
    {
        $date = '';

        if ($opening) {
            foreach ($opening as $hour) {
                if (intval($hour[0]) && intval($hour[1])) {
                    $date .= $this->formatHour($hour[0]) . ' - ' . $this->formatHour($hour[1]);
                }

                if (intval($hour[2]) && intval($hour[3])) {
                    $date .= ($date ? ' / ' : '') . $this->formatHour($hour[2]) . ' - ' . $this->formatHour($hour[3]);
                }
            }
        }

        return $date ?: null;
    }

    /**
     * Format hour
     *
     * @param string $hour
     * @return string
     */
    protected function formatHour($hour)
    {
        return substr($hour, 0, 2) . 'h' . substr($hour, 2, 2);
    }
}
