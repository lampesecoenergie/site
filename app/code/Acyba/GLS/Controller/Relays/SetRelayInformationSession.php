<?php

namespace Acyba\GLS\Controller\Relays;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Acyba\GLS\Helper\Tools;


class SetRelayInformationSession extends Action
{

    protected $_checkoutSession;
    protected $_helperTools;


    /**
     * SetRelayInformationSession constructor.
     * @param Context $context
     * @param Session $checkoutSession
     * @param Tools $helperTools
     */
    public function __construct(Context $context, Session $checkoutSession, Tools $helperTools)
    {
        $this->_checkoutSession = $checkoutSession;
        $this->_helperTools = $helperTools;

        return parent::__construct($context);
    }

    public function execute()
    {
        $relayInformation = [];

        $relayInformation['id'] = $this->getRequest()->getParam('relayId') ?: '';
        $relayInformation['name'] = $this->getRequest()->getParam('relayName') ?: '';
        $relayInformation['address'] = $this->getRequest()->getParam('relayAddress') ?: '';
        $relayInformation['post_code'] = $this->getRequest()->getParam('relayPostCode') ?: '';
        $relayInformation['city'] = $this->getRequest()->getParam('relayCity') ?: '';

        if (!empty($this->_checkoutSession->getGlsRelayInformation())) {
            $this->_checkoutSession->setGlsRelayInformation([]);
        }

        if (array_search("", $relayInformation) === false) {
            $this->_checkoutSession->setGlsRelayInformation($relayInformation);
        } else {
            $this->_helperTools->glsLog(__('Error GLS : Can\'t set relay information in the session for the order because at least one relay information is empty in request.'),
                'err');
        }
    }
}