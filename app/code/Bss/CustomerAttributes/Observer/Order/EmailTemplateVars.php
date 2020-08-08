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
namespace Bss\CustomerAttributes\Observer\Order;

/**
 * Class EmailTemplateVars
 *
 * @package Bss\CustomerAttributes\Observer\Order
 */
class EmailTemplateVars implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Bss\CustomerAttributes\Helper\GetHtmltoEmail
     */
    protected $helper;

    /**
     * EmailTemplateVars constructor.
     * @param \Bss\CustomerAttributes\Helper\GetHtmltoEmail $helper
     */
    public function __construct(\Bss\CustomerAttributes\Helper\GetHtmltoEmail $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $transport = $observer->getTransport();
        $order = $transport['order'];
        $transport['bss_customer_attributes'] = $this->helper->getVariableEmailHtml($order->getCustomerId());
    }
}
