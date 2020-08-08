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
 * Class AjaxStartUpload
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\JobScheduler
 */
class AjaxStartUpload extends Action
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
     * AjaxStartUpload constructor.
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
        $startUploadResponse = false;
        $message = [];
        $message['error'] = "";
        $message['success'] = "";

        $key = $this->getRequest()->getParam('index');
        $totalChunk = $this->_session->getFeedIdsForStartUpload();
        $index = $key + 1;
        if (count($totalChunk) <= $index) {
            $this->_session->unsFeedIdsForStartUpload();
        }
        try {
            if (isset($totalChunk[$key])) {
                $bulkFeedIds = $totalChunk[$key];
                $bulkFeedCollection = $this->feedCollection->create()
                    ->addFieldToFilter('id', array( 'in' => $bulkFeedIds));
                if ($bulkFeedCollection->getSize() > 0) {
                    $startUploadResponse = $this->fileUploadHelper->startUploadJob($bulkFeedCollection);
                    if (isset($startUploadResponse['success']) && $startUploadResponse['success'] == true) {
                        $message['success'] = $message['success'] . "Batch: " . $index . " Jobs Started SuccessFully.";
                    } elseif(isset($startUploadResponse['error'])) {
                        $message['error'] = $message['error'] . 'Batch: ' . $index . ' Failed. Start Upload Job Has Some Errors : ' . $startUploadResponse['error'];
                    } else {
                        $message['error'] = $message['error'] . 'Batch: ' . $index . ' Somthing Went Wrong. Please try again.';
                    }
                } elseif ($bulkFeedCollection->getSize() <= 0) {
                    $message['error'] = $message['error'] . 'Batch: ' . $index . ' No Recored Found To Start Upload.';
                }
            }
        } catch (\Exception $e) {
            $message['error'] = $e->getMessage();
            $this->logger->addError($message['error'], ['path' => __METHOD__]);
        }
        return $resultJson->setData($message);
    }
}
