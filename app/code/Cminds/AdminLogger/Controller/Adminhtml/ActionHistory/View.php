<?php

namespace Cminds\AdminLogger\Controller\Adminhtml\ActionHistory;

use Cminds\AdminLogger\Controller\Adminhtml\AbstractActionHistory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class View
 *
 * @package Cminds\AdminLogger\Controller\Adminhtml\ActionHistory
 */
class View extends AbstractActionHistory
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var object|null
     */
    private $resultPage;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * View constructor.
     *
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        //Get current action history id and then put it into the registry
        $actionId = $this->getRequest()->getParam('id');
        $this->registry->register('cminds_adminlogger_action_id', $actionId);

        //Call page factory to render layout and page content
        $this->setPageData();

        return $this->getResultPage();
    }

    /**
     * Get ResultPage.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    private function getResultPage()
    {
        if ($this->resultPage === null) {
            $this->resultPage = $this->resultPageFactory->create();
        }

        return $this->resultPage;
    }

    /**
     * Set Page Data.
     *
     * @return $this
     */
    private function setPageData()
    {
        $resultPage = $this->getResultPage();
        $resultPage->setActiveMenu('Magento_Catalog::security');
        $resultPage->getConfig()->getTitle()->prepend((__('Admin Actions History - Details')));

        //Add bread crumb
        $resultPage->addBreadcrumb(__('AdminLogger'), __('Admin Actions History - Details'));
        $resultPage->addBreadcrumb(__('AdminLogger'), __('Admin Actions History - Details'));

        return $this;
    }
}
