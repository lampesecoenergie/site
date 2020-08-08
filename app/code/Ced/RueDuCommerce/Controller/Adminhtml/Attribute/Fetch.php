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

namespace Ced\RueDuCommerce\Controller\Adminhtml\Attribute;

class Fetch extends \Magento\Backend\App\Action
{
    public $resultJsonFactory;

    public $json;

    public $category;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Json\Helper\Data $json,
        \Ced\RueDuCommerce\Helper\Category $category
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->json = $json;
        $this->category = $category;
    }

    public function execute()
    {
        $response = [
            'message' => [],
            'success' => false
        ];

        $data = $this->getRequest()->getParams();

        $attributes = [];
        if (isset($data['code']) and !empty($data['code'])) {
            $response['success'] = true;
            $attributesm = $this->category->getAttributes(['hierarchy' => $data['code'],'isMandatory' => 1]);
            $attributesr = $this->category->getAttributes(['hierarchy' => $data['code'],'isMandatory' => 0]);
            if(is_array($attributesm) && is_array($attributesr))
            $attributes = array_merge($attributesm,$attributesr);
        }

        $response['attributes'] = $attributes;
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_RueDuCommerce::rueducommerce_orders');
    }
}
