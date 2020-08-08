<?php


namespace Acyba\GLS\Model\Webservice;

use SoapClient;

class RelaysWSService extends SoapClient
{


    /**
     * RelaysWSService constructor.
     * @param mixed $wsdl
     * @param array $options
     */
    public function __construct($wsdl, array $options)
    {
        return parent::__construct($wsdl, $options);
    }

    public function findRelays($parameters)
    {
        return $this->__soapCall('GetParcelShops', [$parameters]);
    }
}