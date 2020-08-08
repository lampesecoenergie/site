<?php

namespace Acyba\GLS\Block;

use Magento\CatalogSearch\Helper\Data;
use Magento\Framework\View\Element\Template\Context;

class Checkout extends \Magento\Framework\View\Element\Template
{

    protected $_template = 'checkout.phtml';

    /**
     * Checkout constructor.
     */
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }
}