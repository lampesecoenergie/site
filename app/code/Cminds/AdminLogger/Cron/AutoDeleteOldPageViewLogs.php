<?php

namespace Cminds\AdminLogger\Cron;

/**
 * Class AutoDeleteOldPageViewLogs
 *
 * @package Cminds\AdminLogger\Cron
 */
class AutoDeleteOldPageViewLogs extends AbstractLogRemover
{
    /**
     * Cron job which will auto remove action logs after days sets in config.
     *
     * @return AutoDeleteOldPageViewLogs
     */
    public function execute()
    {
        if ($this->moduleConfig->isActive() === false
            && $this->moduleConfig->isAutoLogsDeletionEnabled()
        ) {
            return $this;
        }

        $collection = $this->collectionFactory->create();
        $collection->addPageViewFilter();
        $daysToDelete = $this->moduleConfig->getDaysToClearPageViewLogs();

        $this->removeLogs($collection, $daysToDelete);

        return $this;
    }
}
