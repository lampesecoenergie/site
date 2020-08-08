<?php

namespace Acyba\GLS\Model\Webservice;

use Acyba\GLS\Helper\Tools;


class Service
{

    protected $_helperTools;

    private $_urlWsdl;

    /**
     * Service constructor.
     * @param $_helperTools
     */
    public function __construct(Tools $_helperTools)
    {
        $this->_helperTools = $_helperTools;
    }


    /**
     * @return string
     */
    public function getUrlWsdl()
    {
        if (!$this->_urlWsdl) {
            $this->_urlWsdl = "http://www.gls-group.eu/276-I-PORTAL-WEBSERVICE/services/ParcelShopSearch/wsdl/2010_01_ParcelShopSearch.wsdl";
        }

        return $this->_urlWsdl;
    }


    function loadRelays($zipCode, $address, $city, $countryCode)
    {
        $glsUsernameWS = $this->_helperTools->getConfigValue('gls_usernamews', 'gls', 'carriers');
        $glsPasswordWS = $this->_helperTools->getConfigValue('gls_passws', 'gls', 'carriers');

        $wsdl = $this->getUrlWsdl();

        try{
            $relaysWSService = new RelaysWSService(
                $wsdl,
                ['trace' => true]
            );

            $parameters = [
                'Credentials' => [
                    'UserName' => $glsUsernameWS,
                    'Password' => $glsPasswordWS,
                ],
                'Address' => [
                    'Name1' => '',
                    'Name2' => '',
                    'Name3' => '',
                    'Street1' => $address,
                    'BlockNo1' => '',
                    'Street2' => '',
                    'BlockNo2' => '',
                    'ZipCode' => $zipCode,
                    'City' => $city,
                    'Province' => '',
                    'Country' => $countryCode,
                ],
            ];

            $result = $relaysWSService->findRelays($parameters);

            return $result;
        }catch (\SoapFault $fault){
            $this->_helperTools->glsLog('Error WS GLS : '.$fault->getMessage(), 'err');

            return false;
        }
    }
}