<?php

namespace Cminds\AdminLogger\Model\History\Customer;

use Cminds\AdminLogger\Model\History\AbstractObject;
use Cminds\AdminLogger\Model\History\HistoryInterface;

/**
 * Class Update
 *
 * @package Cminds\AdminLogger\Model\History\Customer
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
        $eventObject = $event->getData('customer');
        $customerNewData = $event->getData('customer')->getData();
        $customerOldData = $event->getData('customer_old_data')->getData();
        $changedValues = $this->dataChecker->getDataChanges($eventObject, $customerOldData, $customerNewData);

        return $this->prepareActionData($event, $actionType, $changedValues);
    }
}
