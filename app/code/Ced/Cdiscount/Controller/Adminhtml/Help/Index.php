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

namespace Ced\Cdiscount\Controller\Adminhtml\Help;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 *
 * @package Ced\Cdiscount\Controller\Adminhtml\Help
 */
class Index extends Action
{
    /**
     * ResultPageFactory
     *
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Helper
     *
     * @var PageFactory
     */
    public $helper;

    /**
     * Index constructor.
     *
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /**
 		 * @var \Magento\Backend\Model\View\Result\Page $resultPage 
	     */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Cdiscount::Cdiscount');
        $resultPage->getConfig()->getTitle()->prepend(__('Cdiscount Help and Support'));
        return $resultPage;
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Cdiscount::Cdiscount');
    }
}
