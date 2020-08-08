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

namespace Ced\Amazon\Controller\Adminhtml\Feed;

use Ced\Amazon\Repository\Feed;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class View
 * @package Ced\Amazon
 */
class View extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /** @var Feed  */
    public $feed;

    /**
     * Feeds constructor.
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Feed $feed
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->feed = $feed;
    }

    public function execute()
    {
        $feedId = $this->getRequest()->getParam('feed_id');
        if (!empty($feedId)) {
            /** @var \Ced\Amazon\Model\Feed $feed */
            $feed = $this->feed->getByFeedId($feedId);
            if ($feed->getId() > 0) {
                return $this->_redirect('amazon/feed/view', ['id' => $feed->getId()]);
            } else {
                $this->messageManager->addNoticeMessage("Feed #{$feedId} is not available.");
                return $this->_redirect('amazon/feeds/index');
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Amazon::developer');
        $resultPage->getConfig()->getTitle()->prepend(__('Amazon Feed View'));
        return $resultPage;
    }
}
