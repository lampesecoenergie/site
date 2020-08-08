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

namespace Bss\CustomerAttributes\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class CompositeConfigProvider
 * @package Bss\CustomerAttributes\Model
 */
class CompositeConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    protected $helper;

    /**
     * CompositeConfigProvider constructor.
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $helper
     */
    public function __construct(
        \Bss\CustomerAttributes\Helper\Customerattribute $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfig()
    {
        $output = [];
        $attributeHelper = $this->helper;
        if ($attributeHelper->getConfig('bss_customer_attribute/general/enable')) {
            $config = [];
            $attributeCollection = $attributeHelper->getUserDefinedAttributes();
            foreach ($attributeCollection as $attribute) {
                if ($attribute->getIsRequired() == 1 &&
                    $attributeHelper->isAttribureAddtoCheckout($attribute->getAttributeCode())
                ) {
                    switch ($attribute->getFrontendInput()) {
                        case "multiselect":
                            $config[] = "select[name='bss_customer_attributes[" . $attribute->getAttributeCode() . "][]']";
                            break;
                        case "boolean":
                            $config[] = "select[name='bss_customer_attributes[" . $attribute->getAttributeCode() . "]']";
                            break;
                        case "textarea":
                            $config[] = "textarea[name='bss_customer_attributes[" . $attribute->getAttributeCode() . "]']";
                            break;
                        default:
                            $config[] = "input[name='bss_customer_attributes[" . $attribute->getAttributeCode() . "]']";
                    }
                }
            }
            $output['bssCA']['requireField'] = $config;
        }
        return $output;
    }
}
