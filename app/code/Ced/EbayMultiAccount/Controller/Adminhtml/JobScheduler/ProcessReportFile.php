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
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class ProcessReportFile
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\JobScheduler
 */
class ProcessReportFile extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /** @var \Ced\EbayMultiAccount\Model\ResourceModel\FeedDetails\CollectionFactory */
    public $feedDetailsCollection;
    /**
     * @var Filter
     */
    public $filter;

    const ADMIN_RESOURCE = 'Ced_EbayMultiAccount::EbayMultiAccount';

    /**
     * ProcessReportFile constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Filter $filter
     * @param \Ced\EbayMultiAccount\Model\ResourceModel\FeedDetails\CollectionFactory $feedDetailsCollection
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Filter $filter,
        \Ced\EbayMultiAccount\Model\ResourceModel\FeedDetails\CollectionFactory $feedDetailsCollection
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        $this->feedDetailsCollection = $feedDetailsCollection;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $schedulerChunks = array();
        $feedId = $this->getRequest()->getParam('id');
        if (!empty($feedId)) {
            $feedCollection = $this->feedDetailsCollection->create()
                ->addFieldToFilter('id', $feedId);
            foreach ($feedCollection as $feedData) {
                $reportFilePath = $feedData->getReportFeedFilePath();
                if (is_string($reportFilePath) && $reportFilePath != null && file_exists($reportFilePath)) {
                    $productIds = array_unique(explode(',', $feedData->getProductIds()));
                    $productChunks = array_chunk($productIds, 50);
                    foreach ($productChunks as $productChunk) {
                        $schedulerChunks[] = array($feedData->getId() => implode(',', $productChunk));
                    }
                }
            }
            $this->_session->setFeedIdsForProcessReportFile($schedulerChunks);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Ced_EbayMultiAccount::product');
            $resultPage->getConfig()->getTitle()->prepend(__('Processing Report File'));
            return $resultPage;
        } else {
            $this->messageManager->addErrorMessage(__('No Reports To Process.'));
            return $this->_redirect('ebaymultiaccount/jobScheduler/index');
        }
    }
}
