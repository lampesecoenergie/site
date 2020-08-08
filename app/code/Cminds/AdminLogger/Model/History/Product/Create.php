<?php

namespace Cminds\AdminLogger\Model\History\Product;

use Cminds\AdminLogger\Model\History\AbstractObject;
use Cminds\AdminLogger\Model\History\HistoryInterface;

/**
 * Class Create
 *
 * @package Cminds\AdminLogger\Model\History\Product
 */
class Create extends AbstractObject implements HistoryInterface
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
