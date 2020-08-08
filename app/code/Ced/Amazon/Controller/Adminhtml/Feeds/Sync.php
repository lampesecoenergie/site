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
 * Class Sync
 * @package Ced\Amazon\Controller\Adminhtml\Feeds
 */
class Sync extends Action
{
    /**
     * @var \Ced\Amazon\Api\FeedRepositoryInterface
     */
    public $feed;

    public function __construct(
        Action\Context $context,
        \Ced\Amazon\Api\FeedRepositoryInterface $feed
    ) {
        parent::__construct($context);
        $this->feed = $feed;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $status = $this->feed->sync($id);
        if ($status != false) {
            $this->messageManager->addSuccessMessage('Feed Synced Successfully');
        } else {
            $this->messageManager->addErrorMessage('Feed Sync Failed');
        }

        return $this->_redirect('*/feeds');
    }
}
