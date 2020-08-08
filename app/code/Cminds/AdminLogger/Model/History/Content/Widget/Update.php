<?php

namespace Cminds\AdminLogger\Model\History\Content\Widget;

use Cminds\AdminLogger\Model\History\AbstractObject;
use Cminds\AdminLogger\Model\History\HistoryInterface;

/**
 * Class Update
 *
 * @package Cminds\AdminLogger\Model\History\Content\Widget
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
        $eventObject = $event->getData('cms_widget');

        $cmsWidgetNewData = $event->getData('cms_widget')->getData();
        $cmsWidgetOldData = $event->getData('old_cms_widget_data')->getOrigData();
        $changedValues = $this->dataChecker->getDataChanges($eventObject, $cmsWidgetOldData, $cmsWidgetNewData);

        return $this->prepareActionData($event, $actionType, $changedValues);
    }
}
