<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 5/9/19
 * Time: 4:38 PM
 */

namespace Ced\Amazon\Controller\Adminhtml\Processor;

use Magento\Backend\App\Action;

class Create extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    public $session;

    public $filter;

    public $actions = [];

    public $orderCollectionFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        $actions = []
    ) {
        $this->session =  $context->getSession();
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        $this->actions = $actions;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $type = $this->getRequest()->getParam('type');

        if (empty($type)) {
            $this->messageManager->addErrorMessage('No Action Type Found.');
            $resultRedirect = $this->resultFactory->create('redirect');
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        $actionMessage = $this->getActionMessage($type);

        $backUrl = $this->getBackUrl($type);

        $this->session->setActionType($type);
        $this->session->setActionMessage($actionMessage);
        $this->session->setBackUrl($backUrl);

        $filters = $this->getRequest()->getParam('filters');
        if (!empty($filters)) {
            $ids = $this->filter->getCollection($this->getActionCollection($type));
            if ($ids instanceof \Magento\Framework\Data\Collection\AbstractDb) {
                $ids = $ids->getAllIds();
            }
        } else {
            $this->messageManager->addErrorMessage('No Ids Found.');
            $resultRedirect = $this->resultFactory->create('redirect');
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        $chunkIds = array_chunk($ids, $this->getChunkSize($type));
        $this->session->setChunkIds($chunkIds);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Amazon::Amazon');
        if (isset($actionMessage['title'])) {
            $resultPage->getConfig()->getTitle()->prepend(__($actionMessage['title']));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('Bulk Action'));
        }
        return $resultPage;
    }

    /**
     * Get Action Messages List
     * @param $type
     * @return mixed|null
     */
    private function getActionMessage($type)
    {
        $messages = null;
        if (isset($this->actions[$type]["messages"])) {
            $messages = $this->actions[$type]["messages"];
        }

        return $messages;
    }

    /**
     * Get Collection Object
     * @param $type
     * @return null|\Magento\Framework\Data\Collection\AbstractDb
     */
    private function getActionCollection($type)
    {
        $collection = null;
        if (isset($this->actions[$type]["collection"])) {
            $collection = $this->actions[$type]["collection"];
        }

        return $collection;
    }

    /**
     * Get Chunk Size
     * @param $type
     * @return int
     */
    private function getChunkSize($type)
    {
        $size = 0;
        if (isset($this->actions[$type]["chunk_size"])) {
            $size = $this->actions[$type]["chunk_size"];
        }

        return $size;
    }

    private function getBackUrl($type)
    {
        $backUrl = null;
        if (isset($this->actions[$type]["back_url"])) {
            $backUrl = $this->actions[$type]["back_url"];
        }

        return $backUrl;
    }
}
