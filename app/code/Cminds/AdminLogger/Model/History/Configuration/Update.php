<?php

namespace Cminds\AdminLogger\Model\History\Configuration;

use Cminds\AdminLogger\Model\History\AbstractObject;
use Cminds\AdminLogger\Model\History\HistoryInterface;

/**
 * Class Update
 *
 * @package Cminds\AdminLogger\Model\History\Configuration
 */
class Update extends AbstractObject implements HistoryInterface
{

    /**
     * Save data attached to event.
     *
     * @param $event
     *
     * @return mixed
     */
    public function saveActionData($event)
    {
        $actionType = $event->getData('action_type');
        $eventObject = $event->getData('configuration_update');
        $changedValues = $this->dataChecker->getConfigDataChanges($eventObject);

        return $this->prepareActionData($event, $actionType, $changedValues);
    }
}
