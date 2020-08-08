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

namespace Ced\EbayMultiAccount\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class massDeleteOrders extends \Magento\Backend\App\Action
{
    /**
     * ResultPageFactory
     * @var PageFactory
     */
    public $resultPageFactory;

    public $helper;


    public $filter;

    public $ebaymultiaccountOrdersCollectionFactory;


    public $ebaymultiaccountOrdersFactory;

    /**
     * FailedOrders constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Ced\EbayMultiAccount\Helper\Orders $helper
     */
    public function __construct(
        \Ced\EbayMultiAccount\Model\ResourceModel\Orders\CollectionFactory $ebaymultiaccountOrdersCollectionFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ced\EbayMultiAccount\Model\OrdersFactory $ebaymultiaccountOrdersFactory,
        Context $context,
        PageFactory $resultPageFactory
    )
    {
        $this->filter = $filter;
        $this->ebaymultiaccountOrdersCollectionFactory = $ebaymultiaccountOrdersCollectionFactory;
        $this->ebaymultiaccountOrdersFactory = $ebaymultiaccountOrdersFactory;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $dataPost = $this->getRequest()->getParam('filters');
        if($dataPost) {
            $ebaymultiaccountOrdersModelIds = $this->filter->getCollection($this->ebaymultiaccountOrdersCollectionFactory->create())->getAllIds();
        } else {
            $ebaymultiaccountOrdersModelIds[] = $this->getRequest()->getParam('id');
        }
        
        if(isset($ebaymultiaccountOrdersModelIds)) {
            try {
                foreach ($ebaymultiaccountOrdersModelIds as $ebaymultiaccountOrdersModelId) {
                    $this->ebaymultiaccountOrdersFactory->create()
                        ->load($ebaymultiaccountOrdersModelId)
                        ->delete();
                }
                $count = count($ebaymultiaccountOrdersModelIds);
                if($count) {
                    $this->messageManager->addSuccess(
                        __($count .' Order(s) Delete Succesfully')
                    );
                }
                else {
                    $this->messageManager->addErrorMessage(__(' Order Not Deleted '));
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__(''.$e->getMessage()));
            }
        }
        else {
            $this->messageManager->addErrorMessage(__('Please Select Order '));
        }
        return $this->_redirect('*/*/index');
    }

    /**
     * IsALLowed
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_EbayMultiAccount::EbayMultiAccount');
    }
}