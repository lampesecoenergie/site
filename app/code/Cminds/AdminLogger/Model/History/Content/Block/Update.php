<?php

namespace Cminds\AdminLogger\Model\History\Content\Block;

use Cminds\AdminLogger\Model\History\AbstractObject;
use Cminds\AdminLogger\Model\History\HistoryInterface;

/**
 * Class Update
 *
 * @package Cminds\AdminLogger\Model\History\Content\Block
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
        $eventObject = $event->getData('cms_block');

        $cmsBlockNewData = $event->getData('cms_block')->getStoredData();
        $cmsBlockOldData = $event->getData('cms_block')->getOrigData();
        $changedValues = $this->dataChecker->getDataChanges($eventObject, $cmsBlockOldData, $cmsBlockNewData);

        return $this->prepareActionData($event, $actionType, $changedValues);
    }
}
