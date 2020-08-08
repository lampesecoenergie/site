<?php

namespace Ced\RueDuCommerce\Block\Adminhtml\Request;

class Help extends \Magento\Backend\Block\Template
{
    public $_template="request/help.phtml";

    /**
     * Object Manger
     *
     * @var \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public $objectManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Backend\Block\Template\Context   $context
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Block\Template\Context $context,
        $data = []
    ) {
        $this->objectManager = $objectManager;
        parent::__construct($context, $data);
    }

    /**
     * Magento Contructor
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate($this->_template);
    }
}
