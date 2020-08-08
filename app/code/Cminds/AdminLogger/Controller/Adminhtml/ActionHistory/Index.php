<?php

namespace Cminds\AdminLogger\Controller\Adminhtml\ActionHistory;

use Cminds\AdminLogger\Controller\Adminhtml\AbstractActionHistory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends AbstractActionHistory
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
     * Index constructor.
     *
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
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
     * Set Page Data
     *
     * @return $this
     */
    private function setPageData()
    {
        $resultPage = $this->getResultPage();
        $resultPage->setActiveMenu('Magento_Catalog::security');
        $resultPage->getConfig()->getTitle()->prepend((__('Admin Actions History')));

        //Add bread crumb
        $resultPage->addBreadcrumb(__('AdminLogger'), __('Admin Actions History'));
        $resultPage->addBreadcrumb(__('AdminLogger'), __('Admin Actions History'));

        return $this;
    }
}
