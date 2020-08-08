<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Megamenu
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Megamenu\Controller\Adminhtml\Menu;

/**
 * Class Delete
 *
 * @package Magedelight\Megamenu\Controller\Adminhtml\Menu
 */
class Delete extends \Magento\Backend\App\Action
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magedelight_Megamenu::delete';

    /**
     * @var \Magedelight\Megamenu\Model\Menu
     */
    protected $menuModel;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Megamenu\Model\Menu $menuModel
    ) {
        $this->menuModel = $menuModel;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('menu_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                // init model and delete
                $model = $this->menuModel;
                $model->load($id);
                $title = $model->getMenuName();
                $model->delete($id);
                $this->messageManager->addSuccess(__('The menu has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_megamenu_on_delete',
                    ['title' => $title, 'status' => 'success']
                );
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_megamenu_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['menu_id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find a menu to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
