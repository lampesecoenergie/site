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
 * Class AjaxScheduleBulkInventory
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\JobScheduler
 */
class AjaxScheduleBulkInventory extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;
    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var Logger
     */
    public $logger;

    /**
     * @var \Ced\EbayMultiAccount\Helper\FileUpload
     */
    public $fileUploadHelper;

    /**
     * AjaxScheduleBulkInventory constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param Logger $logger
     * @param \Ced\EbayMultiAccount\Helper\FileUpload $fileUploadHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        Logger $logger,
        \Ced\EbayMultiAccount\Helper\FileUpload $fileUploadHelper
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->fileUploadHelper = $fileUploadHelper;
        $this->logger = $logger;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $accountIdToSchedule = '';
        $resultJson = $this->resultJsonFactory->create();
        $message = [];
        $message['error'] = "";
        $message['success'] = "";

        $key = $this->getRequest()->getParam('index');
        $accountIndexes = $this->_session->getAccountIndexes();
        $totalChunk = $this->_session->getBulkInventoryIds();
        $index = $key + 1;
        foreach ($accountIndexes as $accountId => $accountIndex) {
            if($index <= $accountIndex['end_index'] && $index >= $accountIndex['start_index']) {
                $accountIdToSchedule = $accountId;
            }
        }
        if (count($totalChunk) <= $index) {
            $this->_session->unsBulkInventoryIds();
        }
        try {
            if (isset($totalChunk[$key])) {
                $collectionIds = $totalChunk[$key];
                $collectionIds = is_string($collectionIds) ? array($collectionIds) : $collectionIds;
                $scheduled = $this->fileUploadHelper->createSchedulerForIdsWithAction($collectionIds, 'ReviseInventoryStatus', $accountIdToSchedule);
                if ($scheduled) {
                    $message['success'] = $message['success'] . "Batch: " . $index . " products scheduled for inventory/price syncing";
                } else {
                    $message['error'] = $message['error'] . 'Batch: ' . $index . ' not scheduled for inventory/price syncing.';
                }
            }
        } catch (\Exception $e) {
            $message['error'] = $e->getMessage();
            $this->logger->addError($message['error'], ['path' => __METHOD__]);
        }
        return $resultJson->setData($message);
    }
}
