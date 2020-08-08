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
 *
 * @category   BSS
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Plugin\Model\Metadata;

class Form
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    protected $customerAttribute;

    /**
     * Eav attribute factory
     * @var \Magento\Eav\Model\Config
     */
    protected $eavAttribute;

    /**
     * Form constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $customerattribute
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Bss\CustomerAttributes\Helper\Customerattribute $customerattribute,
        \Magento\Eav\Model\ConfigFactory $eavAttributeFactory
    ) {
        $this->request = $request;
        $this->customerAttribute = $customerattribute;
        $this->eavAttribute = $eavAttributeFactory;
    }

    /**
     * @param mixed $subject
     * @param array $result
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllowedAttributes($subject, $result)
    {
        $page = $this->request->getFullActionName();
        $attributeDefault = ['firstname', 'lastname', 'email','password','taxvat'];
        if ($page == 'customer_account_editPost'
            && $this->customerAttribute->getConfig('bss_customer_attribute/general/enable')
        ) {
            foreach ($result as $attributeCode => $attribute) {
                if (in_array($attributeCode, $attributeDefault)) {
                    continue;
                }
                $attribute = $this->eavAttribute->create()
                    ->getAttribute('customer', $attributeCode);
                $usedInForms = $attribute->getUsedInForms();
                if (!in_array('customer_account_edit_frontend', $usedInForms)) {
                    unset($result[$attributeCode]);
                }
            }
        }
        return $result;
    }
}
