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

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Resend
 * @package Ced\Amazon\Controller\Adminhtml\Feeds
 */
class Resend extends Action
{
    /** @var \Ced\Amazon\Model\ResourceModel\Feed\CollectionFactory  */
    public $collection;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /** @var \Ced\Amazon\Api\FeedRepositoryInterface  */
    public $feed;

    public function __construct(
        Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ced\Amazon\Model\ResourceModel\Feed\CollectionFactory $collectionFactory,
        \Ced\Amazon\Api\FeedRepositoryInterface $feed
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->feed = $feed;
        $this->collection = $collectionFactory;
    }

    public function execute()
    {
        $status = false;
        $isFilter = $this->getRequest()->getParam('filters');
        if (isset($isFilter)) {
            $collection = $this->filter->getCollection($this->collection->create());
            /** @var \Ced\Amazon\Api\Data\FeedInterface $item */
            foreach ($collection->getItems() as $item) {
                $status = $this->feed->resend($item->getId());
            }
        } else {
            $id = $this->getRequest()->getParam('id');
            $status = $this->feed->resend($id);
        }

        if ($status) {
            $this->messageManager->addSuccessMessage('Feed resent successfully.');
        } else {
            $this->messageManager->addErrorMessage('Feed resending failed.');
        }

        $this->_redirect('*/feeds');
    }
}
