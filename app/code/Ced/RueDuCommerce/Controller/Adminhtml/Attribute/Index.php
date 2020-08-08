<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 12/1/18
 * Time: 3:51 PM
 */

namespace Ced\RueDuCommerce\Controller\Adminhtml\Attribute;

class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Index constructor.
     *
     * @param \Magento\Backend\App\Action\Context        $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_RueDuCommerce::RueDuCommerce');
        $resultPage->getConfig()->getTitle()->prepend(__('RueDuCommerce Category Attributes'));
        return $resultPage;
    }

    /**
     * @return mixed
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_RueDuCommerce::RueDuCommerce');
    }
}
