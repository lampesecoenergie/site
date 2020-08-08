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
 * @package   Ced_m2.1.9
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Controller\Adminhtml\Category;


use Magento\Framework\App\Action\Context;

class Fetch extends \Magento\Framework\App\Action\Action
{

    public $category;
    public $jsonFactory;

    public function __construct(
        Context $context,
        \Ced\Cdiscount\Helper\Category $category,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        $this->jsonFactory = $resultJsonFactory;
        $this->category = $category;
        parent::__construct($context);
    }

    public function execute()
    {
        $response = [
          'success' => false,
          'data' => []
        ];
        $jsonFactory = $this->jsonFactory->create();
        try {
            $saved = $this->category->saveCategoriesTree();
            $response['success'] = true;
            $response['data'] = $saved;
            return $jsonFactory->setData($response);
        }catch (\Exception $exception) {
            $response['success'] = false;
            $response['data'] = $exception->getMessage();
            return $jsonFactory->setData($response);
        }
    }
}