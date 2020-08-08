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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Plugin\Integration\Model\ResourceModel;

/**
 * Class CustomerRepository
 * @package Bss\CustomerAttributes\Plugin\Model\ResourceModel
 */
class CustomerRepository
{
    /**
     * @var \Magento\Framework\Registry $registry
     */
    protected $registry;

    /**
     * @var \Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper
     */
    private $b2BRegistrationIntegration;

    /**
     * CustomerRepository constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper $b2BRegistrationIntegration
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper $b2BRegistrationIntegration
    ) {
        $this->registry = $registry;
        $this->b2BRegistrationIntegration = $b2BRegistrationIntegration;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param null $passwordHash
     * @return mixed
     */
    public function aroundSave(
        $subject,
        callable $proceed,
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        $passwordHash = null
    ) {
        if ($this->b2BRegistrationIntegration->isB2BRegistrationModuleEnabled()) {
            if ($this->registry->registry('bss_customer')) {
                $this->registry->unregister('bss_customer');
                $this->registry->register('bss_customer', $customer);
            } else {
                $this->registry->register('bss_customer', $customer);
            }
        }

        return $proceed($customer, $passwordHash);
    }
}
