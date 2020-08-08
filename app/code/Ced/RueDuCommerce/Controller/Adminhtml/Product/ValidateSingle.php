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
 * @package   Ced_RueDuCommerce
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RueDuCommerce\Controller\Adminhtml\Product;

/**
 * Class View
 *
 * @package Ced\RueDuCommerce\Controller\Adminhtml\Product
 */
class ValidateSingle extends  \Magento\Backend\App\Action
{
    public $registry;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * @var \Ced\RueDuCommerce\Helper\Product
     */
    public $rueducommerce;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    public $catalogCollection;

    /**
     * Json Factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    /**
     * ValidateSingle constructor.
     *
     * @param \Magento\Backend\App\Action\Context              $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Ui\Component\MassAction\Filter          $filter
     * @param \Magento\Framework\Registry                      $registry
     * @param \Magento\Catalog\Model\Product                   $collection
     * @param \Ced\RueDuCommerce\Helper\Config                        $config
     * @param \Ced\RueDuCommerce\Helper\Product                       $product
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product $collection,
        \Ced\RueDuCommerce\Helper\Config $config,
        \Ced\RueDuCommerce\Helper\Product $product
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->filter = $filter;
        $this->catalogCollection = $collection;
        $this->rueducommerce = $product;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $response = $this->rueducommerce->validateAllProducts([$id]);
        if ($response) {
            $this->messageManager->addSuccessMessage(' Product(s) Validation Process Executed successfully.');
        } else {
            $message = 'Product Validate Failed.';
            $errors = $this->registry->registry('rueducommerce_product_errors');
            if (isset($errors)) {
                $message = "Product Validate Failed. \nErrors: " . (string)json_encode($errors);
            }
            $this->messageManager->addError($message);
        }

        $resultRedirect = $this->resultFactory->create('redirect');
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
