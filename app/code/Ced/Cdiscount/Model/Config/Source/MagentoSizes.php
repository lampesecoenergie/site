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

namespace Ced\Cdiscount\Model\Config\Source;


class MagentoSizes implements \Magento\Framework\Data\OptionSourceInterface
{

    public $currencyModel;
    public $eavAttribute;
    public $eavConfig;

    public function __construct(
        \Magento\Directory\Model\Currency $currencyModel,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $eavattribute,
        \Magento\Eav\Model\Config $config
    ) {
        $this->eavConfig = $config;
        $this->eavAttribute = $eavattribute;
        $this->currencyModel = $currencyModel;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $returnData = [];
        $attrType = $this->eavAttribute->create()->getCollection()
            ->addFieldToFilter('attribute_code', ['in' => ['taobao_size','shopify_size']])
            ->addFieldToFilter('frontend_input', ['eq' => 'select'])
            ->getData();
        if (isset($attrType) && !empty($attrType)) {
            foreach ($attrType as $value) {
                $optionsObject = $this->eavConfig->getAttribute('catalog_product',
                    $value['attribute_code'])
                    ->getSource();
                $returnData = array_merge_recursive($returnData, $optionsObject->getAllOptions());
            }
        }
        return $returnData;
    }
}