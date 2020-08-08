<?php

namespace Cminds\AdminLogger\Controller\Adminhtml\ActionHistory;

use Cminds\AdminLogger\Controller\Adminhtml\AbstractActionHistory;
use Cminds\AdminLogger\Model\AdminLoggerFactory;
use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Backend\App\Action\Context;

/**
 * Class Delete
 *
 * @package Cminds\AdminLogger\Controller\Adminhtml\ActionHistory
 */
class Delete extends AbstractActionHistory
{
    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var AdminLoggerFactory
     */
    private $adminLoggerFactory;

    /**
     * Delete constructor.
     *
     * @param AdminLoggerFactory $adminLoggerFactory
     * @param ModuleConfig       $moduleConfig
     * @param Context            $context
     */
    public function __construct(
        AdminLoggerFactory $adminLoggerFactory,
        ModuleConfig $moduleConfig,
        Context $context
    ) {
        $this->adminLoggerFactory = $adminLoggerFactory;
        $this->moduleConfig = $moduleConfig;

        parent::__construct($context);
    }

    public function execute()
    {
        $execute = true;

        if ($this->moduleConfig->isActive() === false) {
            $execute = false;
            $this->messageManager->addErrorMessage(
                __('Admin Logger module is disabled in configuration.')
            );
        }

        if ($execute === true
            && $this->moduleConfig->isLogsDeletionEnabled() === false
        ) {
            $execute = false;
            $this->messageManager->addErrorMessage(
                __('Admin Logger deletion feature is disabled in configuration.')
            );
        }

        if ($execute === true) {
            try {
                $logCollection = $this->adminLoggerFactory->create();
                $logId = $this->getRequest()->getParam('id');
                $logCollection->load($logId)->delete();

                $this->messageManager->addSuccessMessage(
                    __('We deleted successful log.')
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('adminlogger/actionhistory/index');
    }
}
