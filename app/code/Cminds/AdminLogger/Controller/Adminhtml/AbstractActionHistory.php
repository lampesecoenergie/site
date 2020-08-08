<?php

namespace Cminds\AdminLogger\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Class AbstractActionHistory
 *
 * @package Cminds\AdminLogger\Controller\Adminhtml
 */
abstract class AbstractActionHistory extends Action
{
    /**
     * Check permission via ACL resource.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cminds_AdminLogger::history');
    }
}
