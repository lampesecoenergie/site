<?php

namespace Cminds\AdminLogger\Controller\Adminhtml\ActionHistory;

use Cminds\AdminLogger\Controller\Adminhtml\AbstractActionHistory;
use Cminds\AdminLogger\Model\ResourceModel\AdminLogger\CollectionFactory;
use Cminds\AdminLogger\Model\Config as ModuleConfig;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassDelete
 *
 * @package Cminds\AdminLogger\Controller\Adminhtml\ActionHistory
 */
class MassDelete extends AbstractActionHistory
{

    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * MassDelete constructor.
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     * @param ModuleConfig      $moduleConfig
     * @param Context           $context
     */
    public function __construct(
        Filter $filter,
        CollectionFactory $collectionFactory,
        ModuleConfig $moduleConfig,
        Context $context
    ) {
        $this->_filter = $filter;
        $this->collectionFactory = $collectionFactory;
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
                $logCollection = $this->_filter->getCollection($this->collectionFactory->create());
                $countDeletedLogs = 0;

                foreach ($logCollection as $item) {
                    $item->delete();
                    $countDeletedLogs++;
                }
                $countNonDeletedLogs = $logCollection->count() - $countDeletedLogs;

                if ($countNonDeletedLogs && $countDeletedLogs) {
                    $this->messageManager->addErrorMessage(
                        __('%1 log(s) cannot be deleted.', $countNonDeletedLogs)
                    );
                } elseif ($countNonDeletedLogs) {
                    $this->messageManager->addErrorMessage(
                        __('You cannot delete %1 the log(s).', $countNonDeletedLogs)
                    );
                }

                if ($countDeletedLogs) {
                    $this->messageManager->addSuccessMessage(
                        __('We deleted %1 log(s).', $countDeletedLogs)
                    );
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('adminlogger/actionhistory/index');
    }
}
