<?php

namespace Cminds\AdminLogger\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class ActionHistory
 *
 * @package Cminds\AdminLogger\Block\Adminhtml
 */
class ActionHistory extends Container
{
    /**
     * ActionHistory constructor.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_actionhistory';
        $this->_blockGroup = 'Cminds_AdminLogger';
        $this->_headerText = __('Admin Actions History');

        parent::_construct();
    }
}
