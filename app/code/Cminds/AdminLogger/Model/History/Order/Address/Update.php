<?php

namespace Cminds\AdminLogger\Model\History\Order\Address;

use Cminds\AdminLogger\Model\History\AbstractObject;
use Cminds\AdminLogger\Model\History\HistoryInterface;

/**
 * Class Update
 *
 * @package Cminds\AdminLogger\Model\History\Order\Address
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
        $eventObject = $event->getData('address');

        $addressNewData = $eventObject->getData();
        $addressOldData = $eventObject->getStoredData();
        $changedValues = $this->dataChecker->getDataChanges($eventObject, $addressOldData, $addressNewData);

        return $this->prepareActionData($event, $actionType, $changedValues);
    }
}
