<?php
namespace BoostMyShop\AdminLogger\Block\Log;

class Edit extends \Magento\Backend\Block\Template
{
    protected $_template = 'Log/Edit.phtml';

    protected $_coreRegistry = null;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, array $data = [])
    {
        parent::__construct($context, $data);

        $this->_coreRegistry = $registry;
    }

    public function getLog()
    {
        return $this->_coreRegistry->registry('current_adminlogger_log');
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/index');
    }
}