<?php

namespace Cminds\AdminLogger\Model\History;

/**
 * Interface HistoryInterface
 *
 * @package Cminds\AdminLogger\Model\History
 */
interface HistoryInterface
{
    /**
     * Save data attached to event.
     *
     * @param $event
     *
     * @return mixed
     */
    public function saveActionData($event);
}
