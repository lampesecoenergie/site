<?php

namespace Potato\Crawler\Controller\Adminhtml\Process;

use Magento\Backend\App\Action;
use Potato\Crawler\Model\Config;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Run
 */
class Run extends Action
{
    const ADMIN_RESOURCE = 'Potato_Crawler::po_crawler';

    /** @var StoreManagerInterface  */
    protected $storeManager;
    
    /** @var Config  */
    protected $config;
    
    /**
     * Run constructor.
     * @param Action\Context $context
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     */
    public function __construct(
        Action\Context $context,
        StoreManagerInterface $storeManager,
        Config $config
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $storeCode = $this->getRequest()->getParam('store', null);
        $websiteCode = $this->getRequest()->getParam('website', null);
        $stores = $this->config->getStoresQueue();
        if ($storeCode) {
            $stores = [$this->storeManager->getStore($storeCode)];
        }
        if ($websiteCode) {
            $stores = $this->storeManager->getWebsite($websiteCode)->getStores();
        }

        $this->_eventManager->dispatch(
            'potato_crawler_add_to_queue',
            ['stores' => $stores]
        );
        $this->messageManager->addSuccessMessage(__('Store urls will be added to queue by the next cron running.'));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setRefererUrl();
    }
}