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
namespace Bss\CustomerAttributes\Block\Frontend\Order;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

/**
 * Class Info
 *
 * @package Bss\CustomerAttributes\Block\Frontend\Order
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $sessionCustomer;
    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    protected $helper;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Info constructor.
     * @param Template\Context $context
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $helper
     * @param \Magento\Customer\Model\SessionFactory $sessionCustomer
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Bss\CustomerAttributes\Helper\Customerattribute $helper,
        \Magento\Customer\Model\SessionFactory $sessionCustomer,
        Registry $registry,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->sessionCustomer = $sessionCustomer;
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * Get Customer
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->sessionCustomer->create()->getCustomer();
    }

    /**
     * Get Helper
     *
     * @return \Bss\CustomerAttributes\Helper\Customerattribute
     */
    public function resultHelper()
    {
        return $this->helper;
    }
}
