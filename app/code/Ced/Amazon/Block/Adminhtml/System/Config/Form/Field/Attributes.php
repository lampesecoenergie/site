<?php


/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Lazada
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Block\Adminhtml\System\Config\Form\Field;


use Magento\Framework\Api\SearchCriteriaBuilder;

class Attributes extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var
     */
    private $_store;

    private  $searchCriteriaBuilder;

    private  $attributeCollection;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollection,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->attributeCollection = $attributeCollection;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return array|null
     */
    protected function _getShippingMethod()
    {
        if ($this->_store === null) {
            $shipMethod = $this->attributeCollection->create()->getItems();
            $allowedAttrsType = ['price', 'text'];
            foreach ($shipMethod as $attribute) {
                if (in_array($attribute->getFrontendInput(), $allowedAttrsType) &&
                    !empty($attribute->getFrontendLabel())) {
                    $magentoattributeCodeArray[] = [
                            'label' => $attribute->getFrontendLabel(),
                            'value' => $attribute->getAttributecode()
                    ];
                }
            }
            $this->_store = $magentoattributeCodeArray;
        }
        return $this->_store;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getShippingMethod() as  $method) {
                $this->addOption($method['value'], addslashes($method['label']));
            }
        }
        return parent::_toHtml();
    }
}
