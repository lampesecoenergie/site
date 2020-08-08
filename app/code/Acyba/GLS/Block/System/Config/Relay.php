<?php

namespace Acyba\GLS\Block\System\Config;


use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Relay extends Field
{


    /**
     * Relay constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $abstractElement
     * @return mixed
     */
    protected function _getElementHtml(AbstractElement $abstractElement)
    {
        return '<div style="color: red; font-weight: bold">' . __('GLS POINT RELAY SHIPPING METHOD NEEDS A GOOGLE MAPS API KEY') . '</div>';
    }
}