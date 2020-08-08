<?php

namespace BoostMyShop\AdminLogger\Controller\Adminhtml\Main;

class Index extends \BoostMyShop\AdminLogger\Controller\Adminhtml\Main
{

    /**
     * @return void
     */
    public function execute()
    {

        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Admin Logger'));
        $this->_view->renderLayout();
    }
}
