<?php

namespace Acyba\GLS\Block;

use Magento\CatalogSearch\Helper\Data;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session;


class ListRelays extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'list_relays.phtml';

    private $_listRelays = [];

    /**
     * ListRelays constructor.
     */
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getListRelays()
    {
        return $this->_listRelays;
    }

    /**
     * @param array $listRelays
     */
    public function setListRelays(array $listRelays)
    {
        $this->_listRelays = $listRelays;
    }

    public function formatRelaysOpeningHours($hour)
    {
        if (is_string($hour) && strlen($hour) == 6) {
            $fourIntsHour = substr($hour, 0, 4);
            $formatHour = substr($fourIntsHour, 0, 2).'h'.substr($fourIntsHour, 2);

            return $formatHour;
        }else {
            return '';
        }
    }
}