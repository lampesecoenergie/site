<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_EbayMultiAccount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Controller\Adminhtml\JobScheduler;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Ced\EbayMultiAccount\Helper\Logger;

/**
 * Class AjaxSyncJob
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\JobScheduler
 */
class AjaxSyncJob extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;
    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /** @var \Ced\EbayMultiAccount\Model\FeedDetails  */
    public $feedCollection;

    /**
     * @var Logger
     */
    public $logger;

    /** @var \Ced\EbayMultiAccount\Helper\FileUpload */
    public $fileUploadHelper;

    /**
     * AjaxSyncJob constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param Logger $logger
     * @param \Ced\EbayMultiAccount\Helper\FileUpload $fileUploadHelper
     * @param \Ced\EbayMultiAccount\Model\ResourceModel\FeedDetails\CollectionFactory $schedulerCollection
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        Logger $logger,
        \Ced\EbayMultiAccount\Helper\FileUpload $fileUploadHelper,
        \Ced\EbayMultiAccount\Model\ResourceModel\FeedDetails\CollectionFactory $schedulerCollection
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->fileUploadHelper = $fileUploadHelper;
        $this->logger = $logger;
        $this->feedCollection = $schedulerCollection;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $syncResponse = false;
        $message = [];
        $message['error'] = "";
        $message['success'] = "";

        $key = $this->getRequest()->getParam('index');
        $totalChunk = $this->_session->getFeedIdsForSync();
        $index = $key + 1;
        if (count($totalChunk) <= $index) {
            $this->_session->unsFeedIdsForSync();
        }
        try {
            if (isset($totalChunk[$key])) {
                $bulkFeedIds = $totalChunk[$key];
                $bulkFeedCollection = $this->feedCollection->create()
                    ->addFieldToFilter('id', array( 'in' => $bulkFeedIds));
                if ($bulkFeedCollection->getSize() > 0) {
                    $syncResponse = $this->fileUploadHelper->syncJobs($bulkFeedCollection);
                    if (isset($syncResponse['success']) && $syncResponse['success'] == true) {
                        $message['success'] = $message['success'] . "Batch: " . $index . " Jobs Synced SuccessFully.";
                    } elseif(isset($syncResponse['error'])) {
                        $message['error'] = $message['error'] . 'Batch: ' . $index . ' Failed. Job Sync Has Some Errors : ' . $syncResponse['error'];
                    } else {
                        $message['error'] = $message['error'] . 'Batch: ' . $index . ' Somthing Went Wrong. Please try again.';
                    }
                } elseif ($bulkFeedCollection->getSize() <= 0) {
                    $message['error'] = $message['error'] . 'Batch: ' . $index . ' Somthing Went Wrong. Please try again.';
                }
            }
        } catch (\Exception $e) {
            $message['error'] = $e->getMessage();
            $this->logger->addError($message['error'], ['path' => __METHOD__]);
        }
        return $resultJson->setData($message);
    }
}
