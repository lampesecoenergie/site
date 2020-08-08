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
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Product;

/**
 * Class View
 * @package Ced\Amazon\Controller\Adminhtml\Product
 */
class View extends \Magento\Backend\App\Action
{
    /**
     * @var \Ced\Amazon\Helper\Product
     */
    public $product;

    /**
     * Json Factory
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ced\Amazon\Helper\Product $product
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->product = $product;
    }

    public function execute()
    {
        $products = [];
        $sku = $this->getRequest()->getParam('sku');
        $id = $this->getRequest()->getParam('id');

        if (!empty($sku) && !empty($id)) {
            $products = $this->product->getProduct($id, $sku);
        }

        return $this->resultJsonFactory
            ->create()
            ->setData($products);
    }
}
