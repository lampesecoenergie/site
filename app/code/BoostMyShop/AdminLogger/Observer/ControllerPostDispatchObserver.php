<?php

namespace BoostMyShop\AdminLogger\Observer;

use Magento\Framework\Event\ObserverInterface;

class ControllerPostDispatchObserver extends AbstractObserver
{

    /**
     * Append review summary before rendering html
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    protected function _execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->getConfig()->logVisited()) {
            $request = $observer->getEvent()->getrequest();

            if (in_array($request->getFullActionName(), $this->getConfig()->getRoutesToExclude()))
                return $this;;

            $details = $this->formatRoute($request->getFullActionName());

            $objectId = $this->getObjectId($request);
            if ($objectId)
                $details .= ' (id: '.$objectId.')';

            $obj = $this->_logFactory->create();
            $obj->setal_object_type('page')->setal_action('view')->setal_details($details);
            $obj->save();
        }


        return $this;
    }

    protected function formatRoute($route)
    {
        $route = str_replace('_', '/', $route);

        return $route;
    }

    protected function getObjectId($request)
    {
        $all = $request->getParams();

        foreach($this->_configFactory->create()->getObjectIdParams() as $item)
        {
            if (isset($all[$item]))
                return $all[$item];
        }

        return false;
    }
}
