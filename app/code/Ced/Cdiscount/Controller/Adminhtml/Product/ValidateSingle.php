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

namespace Ced\Cdiscount\Controller\Adminhtml\Product;

/**
 * Class View
 *
 * @package Ced\Cdiscount\Controller\Adminhtml\Product
 */
class ValidateSingle extends \Magento\Backend\App\Action
{
    public $registry;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * @var \Ced\Cdiscount\Helper\Product
     */
    public $cdiscount;

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

    public $redirectFactory;

    /**
     * ValidateSingle constructor.
     *
     * @param \Magento\Backend\App\Action\Context              $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Ui\Component\MassAction\Filter          $filter
     * @param \Magento\Framework\Registry                      $registry
     * @param \Magento\Catalog\Model\Product                   $collection
     * @param \Ced\Cdiscount\Helper\Product                       $product
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product $collection,
        \Ced\Cdiscount\Helper\Product $product
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->filter = $filter;
        $this->catalogCollection = $collection;
        $this->cdiscount = $product;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $redirect = $this->redirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        $response = $this->cdiscount->validateAllProducts([$id]);
        if ($response) {
            $this->messageManager->addSuccessMessage(' Product(s) Validation run
                     successfully kindly see validation sections');
        } else {
            $message = 'Product Validate Failed.';
            $errors = $this->registry->registry('cdiscount_product_errors');
            if (isset($errors)) {
                $message = "Product Validate Failed. \nErrors: " . (string)json_encode($errors);
            }
            $this->messageManager->addError($message);
        }

        return $redirect->setPath(\Ced\Cdiscount\Controller\Adminhtml\Product\Validate::REDIRECT_PATH);
    }
}
