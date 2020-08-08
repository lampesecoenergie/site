<?php

namespace BoostMyShop\AdminLogger\Observer;

use Magento\Framework\Event\ObserverInterface;

class ModelDeleteAfterObserver extends AbstractModelObserver
{

    /**
     * Append review summary before rendering html
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    protected function _execute(\Magento\Framework\Event\Observer $observer)
    {
        $object = $observer->getEvent()->getObject();
        $objectType = $this->getObjectType($object);

        if ($this->getConfig()->classIsExcluded($objectType))
            return $this;

        $action = 'delete';

        $id = $object->getId();

        $obj = $this->_logFactory->create();
        $obj->setal_object_type($objectType)->setal_action($action)->setal_object_id($id);
        $obj->save();

        return $this;
    }
}
