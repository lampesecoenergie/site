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
 * Class Edit
 *
 * @package Bss\CustomerAttributes\Controller\Adminhtml\Attribute
 */
class Edit extends \Bss\CustomerAttributes\Controller\Adminhtml\Attribute\AbstractAction
{
    /**
     * @var Attribute
     */
    protected $model;

    /**
     * Edit constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Attribute $model
     * @param \Magento\Catalog\Model\Product\Url $productUrl
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Attribute $model,
        \Magento\Catalog\Model\Product\Url $productUrl,
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->model = $model;
        parent::__construct($context, $coreRegistry, $productUrl, $eavEntity, $resultPageFactory);
    }

    /**
     * Init actions
     *
     * @return \Magento\Framework\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Bss_CustomerAttributes::customer_attributes')

            ->addBreadcrumb(__('Customer Attributes'), __('Customer Attributes'))

            ->addBreadcrumb(__('Manage Customer Attributes'), __('Manage Customer Attributes'));

        return $resultPage;
    }

    /**
     * Edit Page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */

    public function execute()
    {
        $attrId = $this->getRequest()->getParam('attribute_id');

        $this->model->setEntityTypeId($this->_entityTypeId);

        if ($attrId) {
            $this->model->load($attrId);
            if (!$this->model->getId()) {
                $this->messageManager->addError(__('This attribute no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('customerattribute/*/');
            }

            // entity type check
            if ($this->model->getEntityTypeId() != $this->_entityTypeId) {
                $this->messageManager->addError(__('This attribute cannot be edited.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('customerattribute/*/');
            }
        }

        // set entered data if was error when we do save
        $data = $this->_session->getAttributeData(true);

        if (!empty($data)) {
            $this->model->addData($data);
        }

        $attributeData = $this->getRequest()->getParam('attribute');
        if (!empty($attributeData) && $attrId === null) {
            $this->model->addData($attributeData);
        }
        $validateRules = $this->model->getValidateRules();
        if (!empty($validateRules)) {
            $this->model->addData($validateRules);
        }
        $this->_coreRegistry->register('entity_attribute', $this->model);

        $item = $this->getItem($attrId);
        $resultPage = $this->createActionPage($item);
        $resultPage->getConfig()->getTitle()->prepend($attrId ? $this->model->getName() : __('New Customer Attribute'));
        $resultPage->getLayout()
            ->getBlock('attribute_edit_js');
        return $resultPage;
    }

    /**
     * Get Item
     *
     * @param int $attrId
     * @return \Magento\Framework\Phrase
     */
    private function getItem($attrId)
    {
        if ($attrId) {
            return __('Edit Customer Attribute');
        } else {
            return __('New Customer Attribute');
        }
    }

    /**
     * Prepare Tittle
     *
     * @param \Magento\Framework\Phrase|null $title
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function createActionPage($title = null)
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('Customer'), __('Customer'))
            ->addBreadcrumb(__('Manage Customer Attributes'), __('Manage Customer Attributes'))
            ->setActiveMenu('Magento_Customer::customer');
        if (!empty($title)) {
            $resultPage->addBreadcrumb($title, $title);
        }
        $resultPage->getConfig()->getTitle()->prepend(__('Customer Attributes'));
        return $resultPage;
    }

    /**
     * Check permission via ACL resource
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_CustomerAttributes::customer_attributes_edit');
    }
}
