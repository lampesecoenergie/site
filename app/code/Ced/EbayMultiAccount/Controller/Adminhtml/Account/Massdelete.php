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

namespace Ced\EbayMultiAccount\Controller\Adminhtml\Account;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Ced\EbayMultiAccount\Model\ResourceModel\Accounts\CollectionFactory;

/**
 * Class Massdelete
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\Account
 */
class Massdelete extends Action
{
    /**
     * @var CollectionFactory
     */
    public $accounts;
    /**
     * @var Filter
     */
    public $filter;

    const ADMIN_RESOURCE = 'Ced_EbayMultiAccount::EbayMultiAccount';

    /**
     * Massdelete constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     */
    public function __construct(
        Context $context,
        CollectionFactory $accounts,
        Filter $filter
    ) {
        parent::__construct($context);
        $this->accounts = $accounts;
        $this->filter = $filter;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ids = $this->filter->getCollection($this->accounts->create())->getAllIds();
        if (!empty($ids)) {
            $collection = $this->accounts->create()->addFieldToFilter('id', ['in' => $ids]);
            if (isset($collection) and $collection->getSize() > 0) {
                $collection->walk('delete');
                $this->messageManager->addSuccessMessage(__($collection->getSize(). ' Account(s) Deleted Successfully'));
            } else {
                $this->messageManager->addErrorMessage(__('No Account available for Delete.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('No Account available for Delete.'));
            
        }
        return $this->_redirect('ebaymultiaccount/account/index');
    }
}
