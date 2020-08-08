<?php

namespace Cminds\AdminLogger\Model\History\Order\Status;

use Cminds\AdminLogger\Model\History\AbstractObject;
use Cminds\AdminLogger\Model\History\HistoryInterface;

/**
 * Class Update
 *
 * @package Cminds\AdminLogger\Model\History\Order\Status
 */
class Update extends AbstractObject implements HistoryInterface
{

    /**
     * Save data attached to event.
     *
     * @param $event
     *
     * @return array
     */
    public function saveActionData($event)
    {
        $actionType = $event->getData('action_type');
        $eventObject = $event->getData('order');
        $status = $event->getData('status');
        $changedValues = [
            'old_value' => ['status' => $status['old_value']],
            'new_value' => ['status' => $status['new_value']]
        ];

        return $this->prepareActionData($event, $actionType, $changedValues);
    }
}
