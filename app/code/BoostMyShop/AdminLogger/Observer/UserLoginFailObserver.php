<?php

namespace BoostMyShop\AdminLogger\Observer;

use Magento\Framework\Event\ObserverInterface;

class UserLoginFailObserver extends AbstractObserver
{

    /**
     * Append review summary before rendering html
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    protected function _execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->getConfig()->logLogins()) {
            $userName = $observer->getEvent()->getuser_name();

            $obj = $this->_logFactory->create();
            $obj->setal_object_type('user')->setal_action('login')->setal_details('Login failed with user name '.$userName);
            $obj->save();
        }

        return $this;
    }
}
