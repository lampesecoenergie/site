<?php

namespace BoostMyShop\AdminLogger\Observer;

use Magento\Framework\Event\ObserverInterface;

abstract class AbstractModelObserver extends AbstractObserver
{

    protected function getObjectType($object)
    {
        $objectType = get_class($object);

        $objectType = str_replace('Model\\', '', $objectType);
        $objectType = str_replace('Magento\\', '', $objectType);
        $objectType = str_replace('Adminhtml\\', '', $objectType);
        $objectType = str_replace('\\Interceptor', '', $objectType);

        $objectType = str_replace('CatalogInventory', '', $objectType);
        $objectType = str_replace('Catalog', '', $objectType);
        $objectType = str_replace('Sales', '', $objectType);

        $objectType = str_replace("\\", " ", $objectType);

        return $objectType;
    }


}
