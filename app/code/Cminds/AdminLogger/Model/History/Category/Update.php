<?php

namespace Cminds\AdminLogger\Model\History\Category;

use Cminds\AdminLogger\Model\History\AbstractObject;
use Cminds\AdminLogger\Model\History\HistoryInterface;

/**
 * Class Update
 *
 * @package Cminds\AdminLogger\Model\History\Category
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
        $eventObject = $event->getData('category');

        $categoryNewData = $event->getData('category')->getData();
        $categoryOldData = $event->getData('category')->getOrigData();
        $changedValues = $this->dataChecker->getDataChanges($eventObject, $categoryOldData, $categoryNewData);

        return $this->prepareActionData($event, $actionType, $changedValues);
    }
}
