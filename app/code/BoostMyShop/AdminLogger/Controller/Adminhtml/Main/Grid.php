<?php

namespace BoostMyShop\AdminLogger\Controller\Adminhtml\Main;

class Grid extends \BoostMyShop\AdminLogger\Controller\Adminhtml\Main
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();

    }
}
