<?php

namespace Cminds\AdminLogger\Cron;

/**
 * Class AutoDeleteOldLoginLogs
 *
 * @package Cminds\AdminLogger\Cron
 */
class AutoDeleteOldLoginLogs extends AbstractLogRemover
{
    /**
     * Cron job which will auto remove login logs after days sets in config.
     *
     * @return AutoDeleteOldLoginLogs
     */
    public function execute()
    {
        if ($this->moduleConfig->isActive() === false
            && $this->moduleConfig->isAutoLogsDeletionEnabled()
        ) {
            return $this;
        }

        $collection = $this->collectionFactory->create();
        $collection->addLoginLogsFilter();
        $daysToDelete = $this->moduleConfig->getDaysToClearLoginLogs();

        $this->removeLogs($collection, $daysToDelete);

        return $this;
    }
}
