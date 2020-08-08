<?php

namespace Ced\Amazon\Block\Adminhtml\Help;

class Support extends \Magento\Backend\Block\Template
{
    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context   $context
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        $data = []
    ) {
        parent::__construct($context, $data);
    }
}
