<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2017 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Model\Pickup;

use MondialRelay\Shipping\Model\Pickup;
use MondialRelay\Shipping\Helper\Data as ShippingHelper;
use MondialRelay\Shipping\Model\Soap;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Data\Collection as DataCollection;

/**
 * Class Collection
 */
class Collection extends DataCollection
{
    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * @var Soap $soap
     */
    protected $soap;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param ShippingHelper $shippingHelper
     * @param Soap $soap
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        ShippingHelper $shippingHelper,
        Soap $soap
    ) {
        parent::__construct($entityFactory);

        $this->shippingHelper = $shippingHelper;
        $this->soap           = $soap;
    }

    /**
     * Retrieve collection items
     *
     * @return \MondialRelay\Shipping\Model\Pickup[]
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * Retrieve collection all items count
     *
     * @return int
     */
    public function getSize()
    {
        return count($this->getItems());
    }

    /**
     * Retrieve Pickup list
     *
     * @param Pickup $object
     * @param string $postcode
     * @param string $country
     * @param string $action
     * @return $this
     */
    public function loadItems($object, $postcode, $country, $action)
    {
        if ($postcode && $action) {
            $data = [
                'Pays'            => $country,
                'CP'              => $postcode,
                'Action'          => $action,
                'NombreResultats' => $this->shippingHelper->getResultNumber()
            ];

            $response = $this->soap->execute('WSI4_PointRelais_Recherche', $data);

            if ($response['error']) {
                return $this;
            }

            if (isset($response['response']->PointsRelais->PointRelais_Details)) {
                $result = $response['response']->PointsRelais->PointRelais_Details;
                if (!is_array($result)) {
                    $result = [$result];
                }

                foreach ($result as $data) {
                    $item = clone $object;
                    $item->setCode($action);
                    foreach ($data as $k => $v) {
                        $key = preg_replace_callback(
                            '/([A-Z])/',
                            function ($m) {
                                return "_" . strtolower($m[1]);
                            },
                            preg_replace("/_/", "", ucwords(strtolower($k), '_'))
                        );
                        $item->setData(trim($key, '_'), $v);
                    }
                    $this->addItem($item);
                }
            }
        }

        return $this;
    }
}
