<?php

namespace BoostMyShop\AdminLogger\Controller\Adminhtml\Main;

class Edit extends \BoostMyShop\AdminLogger\Controller\Adminhtml\Main
{

    /**
     * @return void
     */
    public function execute()
    {

        $logId = $this->getRequest()->getParam('al_id');
        $log = $this->_logFactory->create()->load($logId);

        $this->_coreRegistry->register('current_adminlogger_log', $log);

        $breadcrumb = __('Log view');

        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Log view'));

        $this->_view->renderLayout();
    }
}
