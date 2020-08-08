<?php

namespace Acyba\GLS\Block;

use Magento\CatalogSearch\Helper\Data;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session;

class Selector extends \Magento\Framework\View\Element\Template
{
    protected $_template = "selector.phtml";

    /**
     * Selector constructor.
     */
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    public function getAjaxLoadRelaysUrl()
    {
        return $this->getUrl("gls_relays/relays/LoadRelays");
    }

    public function getAjaxSetInformationRelayUrl()
    {
        return $this->getUrl("gls_relays/relays/SetRelayInformationSession");
    }
}