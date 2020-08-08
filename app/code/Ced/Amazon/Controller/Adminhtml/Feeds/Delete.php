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
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Feeds;

use Ced\Amazon\Model\ResourceModel\Feed\CollectionFactory as FeedCollectionFactory;
use Ced\Amazon\Repository\Feed as FeedRepository;
use Magento\Backend\App\Action;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class Delete
 * @package Ced\Amazon\Controller\Adminhtml\Feeds
 */
class Delete extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var FeedCollectionFactory
     */
    public $feedCollectionFactory;

    /** @var FeedRepository  */
    public $feedRepository;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    public $fileIo;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param File $fileIo
     * @param Filter $filter
     * @param FeedCollectionFactory $feedCollectionFactory
     * @param FeedRepository $feedRepository
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        File $fileIo,
        Filter $filter,
        FeedCollectionFactory $feedCollectionFactory,
        FeedRepository $feedRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->fileIo = $fileIo;
        $this->filter = $filter;
        $this->feedCollectionFactory = $feedCollectionFactory;
        $this->feedRepository = $feedRepository;
    }

    public function execute()
    {
        $isFilter = $this->getRequest()->getParam('filters');
        if (isset($isFilter)) {
            $collection = $this->filter->getCollection($this->feedCollectionFactory->create());
        } else {
            $id = $this->getRequest()->getParam('id');
            if (isset($id) && !empty($id)) {
                $collection = $this->feedCollectionFactory->create()->addFieldToFilter('id', ['eq' => $id]);
            }
        }

        $feedStatus = false;
        if (isset($collection) && $collection->getSize() > 0) {
            $feedStatus = $this->feedRepository->clearRecords(null, $collection);
        }

        if ($feedStatus) {
            $this->messageManager->addSuccessMessage('Feed deleted successfully.');
        } else {
            $this->messageManager->addErrorMessage('Feed delete failed.');
        }

        return $this->_redirect('amazon/feeds');
    }
}
