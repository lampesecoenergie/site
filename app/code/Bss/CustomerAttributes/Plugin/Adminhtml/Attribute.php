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
namespace Bss\CustomerAttributes\Plugin\Adminhtml;

class Attribute
{
    /**
     * @var \Bss\CustomerAttributes\Helper\Data
     */
    protected $data;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Attribute constructor.
     * @param \Bss\CustomerAttributes\Helper\Data $data
     */
    public function __construct(
        \Bss\CustomerAttributes\Helper\Data $data,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->data = $data;
        $this->request = $request;
    }

    /**
     * @param \Magento\Eav\Api\Data\AttributeInterface $subject
     * @param callable $proceed
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetFrontendInput($subject, callable  $proceed)
    {
        if ($this->data->getConfig('bss_customer_attribute/general/enable')
            && $this->request->getFullActionName() == 'customerattribute_attribute_save') {
            if ($proceed() === 'checkboxs') {
                return 'multiselect';
            }
            if ($proceed() === 'radio') {
                return 'select';
            }
        }
        return $proceed();
    }
}
