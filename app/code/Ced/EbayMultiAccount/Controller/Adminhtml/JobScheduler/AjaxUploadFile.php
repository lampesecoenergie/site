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
 * Class AjaxUploadFile
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\JobScheduler
 */
class AjaxUploadFile extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;
    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /** @var \Ced\EbayMultiAccount\Model\JobScheduler  */
    public $schedulerCollection;

    /**
     * @var Logger
     */
    public $logger;

    /** @var \Ced\EbayMultiAccount\Helper\FileUpload */
    public $fileUploadHelper;

    /**
     * AjaxUploadFile constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param Logger $logger
     * @param \Ced\EbayMultiAccount\Helper\FileUpload $fileUploadHelper
     * @param \Ced\EbayMultiAccount\Model\ResourceModel\JobScheduler\CollectionFactory $schedulerCollection
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        Logger $logger,
        \Ced\EbayMultiAccount\Helper\FileUpload $fileUploadHelper,
        \Ced\EbayMultiAccount\Model\ResourceModel\JobScheduler\CollectionFactory $schedulerCollection
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->fileUploadHelper = $fileUploadHelper;
        $this->logger = $logger;
        $this->schedulerCollection = $schedulerCollection;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $fileUploaded = false;
        $message = [];
        $message['error'] = "";
        $message['success'] = "";

        $key = $this->getRequest()->getParam('index');
        $totalChunk = $this->_session->getSchedulerIds();
        $index = $key + 1;
        if (count($totalChunk) <= $index) {
            $this->_session->unsSchedulerIds();
        }
        try {
            if (isset($totalChunk[$key])) {
                $ebaymultiaccountSchedulerIds = $totalChunk[$key];

                $jobSchedulerCollection = $this->schedulerCollection->create()
                    ->addFieldToFilter('id', array( 'in' => $ebaymultiaccountSchedulerIds));
                if ($jobSchedulerCollection->getSize() > 0) {
                    $fileUploaded = $this->fileUploadHelper->uploadPreparedFile($jobSchedulerCollection);
                    if (isset($fileUploaded['success']) && $fileUploaded['success'] == true) {
                        $message['success'] = $message['success'] . "Batch: " . $index . " File Uploaded For Product Creation.";
                    } elseif(isset($fileUploaded['error'])) {
                        $message['error'] = $message['error'] . 'Batch: ' . $index . ' File Uploaded Failed : ' . $fileUploaded['error'];
                    } else {
                        $message['error'] = $message['error'] . 'Batch: ' . $index . ' Somthing Went Wrong. Please Sync Feeds.';
                    }
                } elseif ($jobSchedulerCollection->getSize() <= 0) {
                    $message['error'] = $message['error'] . 'Batch: ' . $index . ' No File Created For Upload.';
                }
            }
        } catch (\Exception $e) {
            $message['error'] = $e->getMessage();
            $this->logger->addError($message['error'], ['path' => __METHOD__]);
        }
        return $resultJson->setData($message);
    }
}
