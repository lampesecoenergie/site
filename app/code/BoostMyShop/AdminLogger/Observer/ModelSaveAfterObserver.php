<?php

namespace BoostMyShop\AdminLogger\Observer;

use Magento\Framework\Event\ObserverInterface;

class ModelSaveAfterObserver extends AbstractModelObserver
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

        if (preg_match('/AdminLogger/i', $objectType))
            return $this;

        $action = $this->getAction($object);

        $id = $object->getId();

        $details = $this->getDetails($object);

        $obj = $this->_logFactory->create();
        $obj->setal_object_type($objectType)->setal_action($action)->setal_object_id($id)->setal_details($details);
        $obj->save();

        return $this;
    }

    protected function getAction($object)
    {
        $action = 'update';

        if ($object->isObjectNew())
            $action = 'create';

        return $action;
    }


    protected function getDetails($object)
    {
        $details = [];
        foreach($object->getData() as $k => $v)
        {
            if (is_object($object->getOrigData($k)))
                continue;
            if (is_array($object->getOrigData($k)))
                continue;

            if (is_object($v))
                continue;
            if (is_array($v))
                continue;

            if ($this->getConfig()->fieldIsExcluded($k))
                continue;

            $oldValue = $object->getOrigData($k);
            $newValue = $v;

            if ($oldValue != $newValue) {
                $details[$k] = ['from' => $oldValue, 'to' => $newValue];
            }

        }

        return json_encode($details);
    }
}
