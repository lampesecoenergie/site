<?php

namespace Potato\Crawler\Controller\Adminhtml\Process;

use Magento\Backend\App\Action;
use Potato\Crawler\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Potato\Crawler\Model\Lock\Warmer as WarmerLock;
use Potato\Crawler\Model\Lock\Queue as QueueLock;

/**
 * Class Restart
 */
class Restart extends Action
{
    const ADMIN_RESOURCE = 'Potato_Crawler::po_crawler';

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /** @var Config  */
    protected $config;

    protected $queueLock;

    protected $warmerLock;

    /**
     * Restart constructor.
     * @param Action\Context $context
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param WarmerLock $warmerLock
     * @param QueueLock $queueLock
     */
    public function __construct(
        Action\Context $context,
        StoreManagerInterface $storeManager,
        Config $config,
        WarmerLock $warmerLock,
        QueueLock $queueLock
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->warmerLock = $warmerLock;
        $this->queueLock = $queueLock;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $type = $this->getRequest()->getParam('type');
        $lock = $this->warmerLock;
        if ($type == 'queue_restart') {
            $lock = $this->queueLock;
        }
        try {
            $lock->restart();
            $this->messageManager->addSuccessMessage(__('Success'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setRefererUrl();
    }
}