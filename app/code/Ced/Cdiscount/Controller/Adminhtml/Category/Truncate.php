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
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 *
 * @package Ced\Cdiscount\Controller\Adminhtml\Cron
 */
class Truncate extends Action
{
    /**
     * @var PageFactory
     */
    public $resultJsonFactory;

    /**
     * @var \Ced\Cdiscount\Model\Feeds
     */
    public $categories;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */


    /**
     * Index constructor.
     *
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ced\Cdiscount\Model\ResourceModel\Category\CollectionFactory $catCollection
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->categories = $catCollection;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $response =  false;
        $jsonFactory = $this->resultJsonFactory->create();
        $collection = $this->categories->create();
        if ($collection->getSize() > 0) {
            $collection->walk('delete');
            $response = true;
        }
        return $jsonFactory->setData($response);
    }

}
