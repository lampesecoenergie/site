<?php

namespace Cminds\AdminLogger\Model\History\Admin\Login;

use Cminds\AdminLogger\Model\History\AbstractObject;
use Cminds\AdminLogger\Model\History\HistoryInterface;

/**
 * Class Fail
 *
 * @package Cminds\AdminLogger\Model\History\Admin\Login
 */
class Fail extends AbstractObject implements HistoryInterface
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

        return $this->prepareActionData($event, $actionType);
    }
}
