<?php

namespace Cminds\AdminLogger\Model\History\Content\Page;

use Cminds\AdminLogger\Model\History\AbstractObject;
use Cminds\AdminLogger\Model\History\HistoryInterface;

/**
 * Class Update
 *
 * @package Cminds\AdminLogger\Model\History\Content\Page
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
        $eventObject = $event->getData('cms_page');

        $cmsPageNewData = $event->getData('cms_page')->getData();
        $cmsPageOldData = $event->getData('cms_page')->getOrigData();
        $changedValues = $this->dataChecker->getDataChanges($eventObject, $cmsPageOldData, $cmsPageNewData);

        return $this->prepareActionData($event, $actionType, $changedValues);
    }
}
