<?php

namespace Cminds\AdminLogger\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class AdminLogger
 *
 * @package Cminds\AdminLogger\Model\ResourceModel
 */
class AdminLogger extends AbstractDb
{
    /**
     * AdminLogger Resource Model initialization.
     *
     */
    protected function _construct()
    {
        $this->_init('cminds_adminlogger_action_history', 'id');
    }
}
