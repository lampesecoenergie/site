<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Controller\Adminhtml\Attribute;

use Magento\Backend\App\Action;
use Magento\Customer\Model\Attribute;

/**
 * Class Delete
 *
 * @package Bss\CustomerAttributes\Controller\Adminhtml\Attribute
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var Attribute
     */
    protected $model;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param Attribute $model
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Attribute $model
    ) {
        $this->model = $model;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('attribute_id');

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($ids) {
            try {
                $this->model->load($ids);
                $this->model->delete();
                $this->messageManager->addSuccessMessage(__('The attribute has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $ids]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a attribute to delete.'));

        return $resultRedirect->setPath('*/*/');
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_CustomerAttributes::customer_attributes_delete');
    }
}
