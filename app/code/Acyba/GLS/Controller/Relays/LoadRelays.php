<?php

namespace Acyba\GLS\Controller\Relays;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Acyba\GLS\Helper\Tools;
use Magento\Framework\Controller\Result\JsonFactory;
use Acyba\GLS\Model\Webservice\RelaysWSService;
use Acyba\GLS\Model\Webservice\Service;
use Magento\Framework\View\Result\PageFactory;
use Magento\Checkout\Model\Session;

class LoadRelays extends Action
{

    protected $_helperTools;
    protected $_resultJsonFactory;
    protected $_service;
    protected $_resultPageFactory;
    protected $_session;

    public function __construct(
        Context $context,
        Tools $helperTools,
        JsonFactory $resultJsonFactory,
        Service $service,
        PageFactory $resultPageFactory,
        Session $session
    ){
        $this->_helperTools = $helperTools;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_service = $service;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_session = $session;

        return parent::__construct($context);
    }

    public function execute()
    {
        $zipCode = $this->getRequest()->getParam('zipCode');
        $address = $this->getRequest()->getParam('address');
        $city = $this->getRequest()->getParam('city');

        //GLS veut, pour l'instant, que seuls les domiciliés en France puissent utiliser la livraison en point relais
        $countryCode = 'FR';

        if (!empty($zipCode) && !empty($countryCode)) {
            $listRelays = $this->_service->loadRelays($zipCode, $address, $city, $countryCode);
            if ($listRelays) {
                if ($listRelays->exitCode->ErrorCode != 0) {
                    $arrayListRelays['errorCode'] = $listRelays->exitCode->ErrorCode;
                    $arrayListRelays['errorDscr'] = $listRelays->exitCode->ErrorDscr;
                }else {

                    $arrayListRelays = [];
                    $i = 0;

                    $weightTotal = 0;

                    $items = $this->_session->getQuote()->getAllVisibleItems();

                    foreach ($items as $item) {
                        $weightTotal += floatval($item->getWeight());
                    }


                    $weightUnit = $this->_helperTools->getConfigValue('weight_unit', 'locale', 'general');

                    //Le poid maximum pour une livraison en point relais classique est 20 kg ou 44 lb, si on a pas d'unité de poids définie
                    //on considère que c'est toujours inférieur à 20 kg

                    if ($weightUnit == "kgs") {
                        $glsMaxWeightAllowed = 20;
                    }else {
                        if ($weightUnit == "lbs") {
                            $glsMaxWeightAllowed = 44;
                        }else {
                            $glsMaxWeightAllowed = true;
                        }
                    }


                    $glsOnlyXlRelay =
                        $this->_helperTools->getConfigValue('gls_onlyxlrelay', 'gls', 'carriers')
                        || ($weightTotal > $glsMaxWeightAllowed);

                    foreach ($listRelays->SearchResults as $oneRelay) {

                        $relayAddressSplit = str_split($oneRelay->Parcelshop->Address->Name1);

                        if (
                            $glsOnlyXlRelay
                            && !(
                                count($relayAddressSplit) == 40
                                && $relayAddressSplit[38] == 'X'
                                && $relayAddressSplit[39] == 'L'
                            )
                        ) {
                            continue;
                        }

                        if (!$this->isDataRelayValid($oneRelay)) {
                            continue;
                        }

                        $arrayListRelays[$i]['relayId'] = $oneRelay->Parcelshop->ParcelShopId;
                        $arrayListRelays[$i]['relayName'] = $oneRelay->Parcelshop->Address->Name1;
                        $arrayListRelays[$i]['relayAddress'] = $oneRelay->Parcelshop->Address->Street1;
                        $arrayListRelays[$i]['relayZipCode'] = $oneRelay->Parcelshop->Address->ZipCode;
                        $arrayListRelays[$i]['relayCity'] = $oneRelay->Parcelshop->Address->City;
                        $arrayListRelays[$i]['relayLatitude'] = $oneRelay->Parcelshop->GLSCoordinates->Latitude;
                        $arrayListRelays[$i]['relayLongitude'] = $oneRelay->Parcelshop->GLSCoordinates->Longitude;

                        if (property_exists($oneRelay->Parcelshop, 'GLSWorkingDay')) {
                            $arrayListRelays[$i]['relayWorkingDays'] = $oneRelay->Parcelshop->GLSWorkingDay;
                        }

                        $i++;
                    }
                }
            }else {
                $arrayListRelays['errorCode'] = '';
                $arrayListRelays['errorDscr'] = __("Error during request GLS WebService. Check logs for more information.");
            }
        }else {
            $arrayListRelays['errorCode'] = '';
            $arrayListRelays['errorDscr'] = __("PostCode and/or Country not defined");
        }


        $resultPage = $this->_resultPageFactory->create();

        $block = $resultPage->getLayout()
            ->createBlock('Acyba\GLS\Block\ListRelays')
            ->setTemplate('Acyba_GLS::list_relays.phtml');


        $block->setListRelays($arrayListRelays);

        $listRelaysHtml = $block->toHtml();

        $resultJson = $this->_resultJsonFactory->create();

        return $resultJson->setData(['html' => $listRelaysHtml]);
    }

    /**
     * @param $relay
     * @return bool
     */
    private function isDataRelayValid($relay)
    {
        if (property_exists($relay, 'Parcelshop')) {
            $glsParcelshop = $relay->Parcelshop;
            if (
                property_exists($glsParcelshop, 'Address')
                && property_exists($glsParcelshop, 'GLSCoordinates')
            ) {
                $glsAddress = $glsParcelshop->Address;
                $glsCoordinates = $glsParcelshop->GLSCoordinates;

                if (
                    property_exists($glsAddress, 'Name1')
                    && property_exists($glsAddress, 'Street1')
                    && property_exists($glsAddress, 'ZipCode')
                    && property_exists($glsAddress, 'City')
                    && property_exists($glsCoordinates, 'Latitude')
                    && property_exists($glsCoordinates, 'Longitude')
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}

