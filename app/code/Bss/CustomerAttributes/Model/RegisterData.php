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

/**
 * Class RegisterData
 * @package Bss\CustomerAttributes\Model
 */
class RegisterData implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    protected $customerAttribute;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * RegisterData constructor.
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $customerAttribute
     * @param \Magento\Checkout\Model\Session $session
     */
    public function __construct(
        \Bss\CustomerAttributes\Helper\Customerattribute $customerAttribute,
        \Magento\Checkout\Model\Session $session
    ) {
        $this->customerAttribute = $customerAttribute;
        $this->session = $session;
    }

    /**
     * @return \Bss\CustomerAttributes\Helper\Customerattribute
     */
    public function getCustomerAttributeHelper()
    {
        return $this->customerAttribute;
    }

    /**
     * @return \Magento\Checkout\Model\Session
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSession()
    {
        return $this->session;
    }
}
