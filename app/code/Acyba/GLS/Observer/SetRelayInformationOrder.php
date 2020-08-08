<?php

namespace Acyba\GLS\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Checkout\Model\Session;
use Acyba\GLS\Helper\Tools;


class SetRelayInformationOrder implements ObserverInterface
{

    protected $_checkoutSession;
    protected $_helperTools;

    public function __construct(Session $checkoutSession, Tools $helperTools)
    {
        $this->_checkoutSession = $checkoutSession;
        $this->_helperTools = $helperTools;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if ($order) {
            $shippingAddress = $order->getShippingAddress();
            $shippingMethod = $order->getShippingMethod();
            $relayInformation = $this->_checkoutSession->getGlsRelayInformation();
            $this->_checkoutSession->setGlsRelayInformation([]);

            if (strpos($shippingMethod, 'gls_relay') !== false) {
                if (!empty($relayInformation) && array_search("", $relayInformation) === false) {
                    $order->setGlsRelayId($relayInformation['id']);
                    $shippingAddress->setCompany($relayInformation['name']);
                    $shippingAddress->setStreet($relayInformation['address']);
                    $shippingAddress->setPostCode($relayInformation['post_code']);
                    $shippingAddress->setCity($relayInformation['city']);
                } else {
                    $this->_helperTools->glsLog(
                        __('Error GLS : Can\'t set relay information in order because at least one information is missing in session'),
                        'err'
                    );
                }
            }
        } else {
            $this->_helperTools->glsLog(
                __('Error GLS : Can\'t set relay information in order because can\'t access to order'),
                'err'
            );
        }
    }
}
