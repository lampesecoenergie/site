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

namespace Ced\EbayMultiAccount\Controller\Adminhtml\AccountConfig;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Ced\EbayMultiAccount\Model\ResourceModel\AccountConfig\CollectionFactory;

/**
 * Class Massdelete
 * @package Ced\EbayMultiAccount\Controller\Adminhtml\AccountConfig
 */
class Massdelete extends Action
{
    /**
     * @var CollectionFactory
     */
    public $accountconfig;
    /**
     * @var Filter
     */
    public $filter;

    const ADMIN_RESOURCE = 'Ced_EbayMultiAccount::EbayMultiAccount';

    /**
     * Massdelete constructor.
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     */
    public function __construct(
        Context $context,
        CollectionFactory $accountconfig,
        Filter $filter
    ) {
        parent::__construct($context);
        $this->accountconfig = $accountconfig;
        $this->filter = $filter;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ids = $this->filter->getCollection($this->accountconfig->create())->getAllIds();
        if (!empty($ids)) {
            $collection = $this->accountconfig->create()->addFieldToFilter('id', ['in' => $ids]);
            if (isset($collection) and $collection->getSize() > 0) {
                $collection->walk('delete');
                $this->messageManager->addSuccessMessage(__($collection->getSize(). ' Account(s) Deleted Successfully'));
            } else {
                $this->messageManager->addErrorMessage(__('No product available for Delete.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('No product available for Delete.'));
            
        }

        return $this->_redirect('ebaymultiaccount/accountconfig/index');
    }
}
