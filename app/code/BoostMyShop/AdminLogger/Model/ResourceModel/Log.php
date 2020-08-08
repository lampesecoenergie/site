<?php

namespace BoostMyShop\AdminLogger\Model\ResourceModel;


class Log extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_adminlogger', 'al_id');
    }


    public function Prune($daysToKeep)
    {
        $connection = $this->getConnection();

        $condition = ['al_created_at < ?' => date('Y-m-d', time() - $daysToKeep * 3600 * 24)];
        $connection->delete($this->getMainTable(), $condition);

        return $this;
    }

    public function Flush()
    {
        $connection = $this->getConnection();

        $connection->delete($this->getMainTable(), []);

        return $this;
    }

}