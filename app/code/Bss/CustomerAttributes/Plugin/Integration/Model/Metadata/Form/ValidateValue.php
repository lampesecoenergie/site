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
namespace Bss\CustomerAttributes\Plugin\Integration\Model\Metadata\Form;

/**
 * Class ValidateValue
 * @package Bss\CustomerAttributes\Plugin\Model\Metadata\Form
 */
class ValidateValue extends \Bss\CustomerAttributes\Plugin\Model\Metadata\Form\ValidateValue
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $area;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Eav\Model\ConfigFactory
     */
    protected $eavAttribute;

    /**
     * @var \Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper
     */
    private $b2BRegistrationIntegration;

    /**
     * ValidateValue constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\State $area
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Eav\Model\ConfigFactory $eavAttributeFactory
     * @param \Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper $b2BRegistrationIntegration
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\State $area,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Eav\Model\ConfigFactory $eavAttributeFactory,
        \Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper $b2BRegistrationIntegration
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->area = $area;
        $this->registry = $registry;
        $this->customerRepository = $customerRepository;
        $this->eavAttribute = $eavAttributeFactory;
        $this->b2BRegistrationIntegration = $b2BRegistrationIntegration;
    }

    /**
     * @param $subject
     * @param $result
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterValidateValue(
        $subject,
        $result
    ) {
        if ($this->b2BRegistrationIntegration->isB2BRegistrationModuleEnabled()) {
            $page = $this->request->getFullActionName();
            $attribute = $this->eavAttribute->create()
                ->getAttribute('customer', $subject->getAttribute()->getAttributeCode());
            if (isset($attribute)) {
                $usedInForms = $attribute->getUsedInForms();
                $enableCustomerAttribute = $this->scopeConfig->getValue(
                    'bss_customer_attribute/general/enable',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );

                if (in_array('is_customer_attribute', $usedInForms) && $attribute->getIsRequired()) {
                    $newB2bValue = "";
                    /* Backend Validate */
                    if ($this->area->getAreaCode() == "adminhtml") {
                        $params = $this->request->getParams();
                        $customer = $this->registry->registry('bss_customer');
                        if ($customer->getId()) {
                            $customerId = $customer->getId();
                            $oldData = $this->customerRepository->getById($customerId);
                            $oldB2b = $oldData->getCustomAttribute('b2b_activasion_status');
                            ($oldB2b) ? $oldB2bValue = $oldB2b->getValue() : "";
                            $newB2bValue = $customer->getCustomAttribute('b2b_activasion_status')->getValue();
                            if ((!$oldB2bValue || !$newB2bValue) && ($oldB2bValue != $newB2bValue)) {
                                return true;
                            }
                        }
                        if ($newB2bValue) {
                            /* B2b account */
                            if (!in_array('b2b_account_create', $usedInForms)) {
                                return true;
                            }
                        } else {
                            /* Normal account */
                            if (!in_array('customer_account_create_frontend', $usedInForms)) {
                                return true;
                            }
                        }
                    }


                    if ((!in_array('b2b_account_create', $usedInForms)
                            || !$enableCustomerAttribute) && $page == 'btwob_account_createpost') {
                        return true;
                    }
                    if ((!in_array('b2b_account_edit', $usedInForms)
                            || !$enableCustomerAttribute) && $page == 'customer_account_editPost') {
                        return true;
                    }
                    if (!in_array('b2b_account_create', $usedInForms)
                        || !in_array('customer_account_create_frontend', $usedInForms)) {
                        return true;
                    }
                }
            }

            return $result;
        }

        return parent::afterValidateValue($subject, $result);
    }
}
