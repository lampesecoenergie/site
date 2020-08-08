<?php

namespace Cminds\AdminLogger\Cron;

/**
 * Class AutoDeleteOldActionLogs
 *
 * @package Cminds\AdminLogger\Cron
 */
class AutoDeleteOldActionLogs extends AbstractLogRemover
{
    /**
     * Cron job which will auto remove action logs after days sets in config.
     *
     * @return AutoDeleteOldActionLogs
     */
    public function execute()
    {
        if ($this->moduleConfig->isActive() === false
            && $this->moduleConfig->isAutoLogsDeletionEnabled()
        ) {
            return $this;
        }

        $collection = $this->collectionFactory->create();
        $collection->addActionLogsFilter();
        $daysToDelete = $this->moduleConfig->getDaysToClearActionLogs();

        $this->removeLogs($collection, $daysToDelete);

        return $this;
    }
}
