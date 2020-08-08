<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Repository;

use Ced\Integrator\Model\Geocode\State;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\HTTP\Adapter\Curl;
use Ced\Integrator\Api\GeocodeRepositoryInterface;
use Ced\Integrator\Helper\Config;
use Ced\Integrator\Api\Data\Geocode\StateInterfaceFactory;

class Geocode implements GeocodeRepositoryInterface
{
    const BASE_URL = "https://maps.googleapis.com/maps/api/geocode/";

    const RESPONSE_TYPE = "json";

    const PARAM_ADDRESS = "address";

    const PARAM_KEY = "key";

    /** @var Config */
    public $config;

    /** @var CurlFactory */
    public $curlFactory;

    /** @var Curl */
    public $client;

    /** @var StateInterfaceFactory  */
    public $stateFactory;

    public function __construct(
        CurlFactory $curlFactory,
        Config $config,
        StateInterfaceFactory $stateInterfaceFactory
    ) {
        $this->config = $config;
        $this->curlFactory = $curlFactory;
        $this->stateFactory = $stateInterfaceFactory;
    }

    public function client()
    {
        if (!isset($this->client)) {
            $this->client = $this->curlFactory->create();
        }

        return $this->client;
    }

    /**
     * Get State By Pincode And City
     * @param string $pincode
     * @param string $city
     * @return \Ced\Integrator\Api\Data\Geocode\StateInterface
     */
    public function getStateByPincodeAndCity($pincode, $city)
    {
        /** @var State $state */
        $state = $this->stateFactory->create();
        if (!empty($pincode) && !empty($city) && $key = $this->config->getGeocodeKey()) {
            $params = [
                self::PARAM_ADDRESS => $city .'+'. $pincode,
                self::PARAM_KEY => $key,
            ];
            $url = self::BASE_URL . self::RESPONSE_TYPE . '?' . http_build_query($params);
            $client = $this->client();
            $client->addOption(CURLOPT_URL, $url);
            $client->addOption(CURLOPT_RETURNTRANSFER, true);
            $client->connect($url);
            $response = $client->read();
            $data = json_decode($response, true);
            if (isset($data['status']) && $data['status'] == 'OK') {
                $addressList = isset($data['results'][0]['address_components']) ?
                    $data['results'][0]['address_components'] : [];
                foreach ($addressList as $value) {
                    if (isset($value['types']) && $value['long_name'] &&
                        strlen($value['short_name']) <= 2 &&
                        (in_array('administrative_area_level_2', $value['types']) ||
                            in_array('administrative_area_level_1', $value['types']))) {
                        $state->addData($value);
                        break;
                    }
                }
            }
        }

        return $state;
    }
}
