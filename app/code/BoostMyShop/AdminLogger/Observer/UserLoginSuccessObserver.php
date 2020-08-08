<?php

namespace BoostMyShop\AdminLogger\Observer;

use Magento\Framework\Event\ObserverInterface;

class UserLoginSuccessObserver extends AbstractObserver
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
            $user = $observer->getEvent()->getUser();

            $obj = $this->_logFactory->create();
            $obj->setal_object_type('user')->setal_action('login')->setal_object_id($user->getId())->setal_details('Login success');
            $obj->save();
        }

        return $this;
    }
}
