<?php

namespace Acyba\GLS\Block\Export;

use Magento\Framework\Data\Form\Element\AbstractElement;


class Orders extends \Magento\Config\Block\System\Config\Form\Field
{


    /**
     * Informations constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

}