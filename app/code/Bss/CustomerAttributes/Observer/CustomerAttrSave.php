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

namespace Bss\CustomerAttributes\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class CustomerAttrSave
 * @package Bss\CustomerAttributes\Observer
 */
class CustomerAttrSave implements ObserverInterface
{
    /**
     * @var array
     */
    protected $attributesArrays = [];

    /**
     * @var GuestToCustomer\Helper\Observer\Helper
     */
    protected $customerFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $entityModel;

    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    protected $helper;

    /**
     * @var \Magento\Customer\Api\CustomerMetadataInterface
     */
    protected $metadata;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * CustomerAttrSave constructor.
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Eav\Model\Entity $entityModel
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $helper
     * @param \Magento\Customer\Api\CustomerMetadataInterface $metadata
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Eav\Model\Entity $entityModel,
        \Bss\CustomerAttributes\Helper\Customerattribute $helper,
        \Magento\Customer\Api\CustomerMetadataInterface $metadata,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->customerFactory = $customerFactory;
        $this->checkoutSession = $checkoutSession;
        $this->entityModel = $entityModel;
        $this->helper = $helper;
        $this->metadata = $metadata;
        $this->json = $json;
        $this->logger = $logger;
    }

    /**
     * @param EventObserver $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->getCustomerId()) {
            $this->SaveForCustomer($order);
        } else {
            $this->SaveForGuest();
        }
        if (!empty($this->attributesArrays)) {
            $order->setCustomerAttribute($this->json->serialize($this->attributesArrays));
            try {
                $order->save();
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }
    }

    /**
     * Save For Customer
     *
     * @param \Magento\Sales\Model\Order $order
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function SaveForCustomer($order)
    {
        $customer = $this->customerFactory->create()->load($order->getCustomerId());
        $customerAttr = $this->checkoutSession->getCustomerAttributes();
        if ($customerAttr && !empty($customerAttr)) {
            $this->checkoutSession->unsCustomerAttributes();
            $customerData = $customer->getDataModel();
            foreach ($customerAttr as $attr => $value) {
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                $customerData->setCustomAttribute($attr, $value);
            }
            $customer->updateData($customerData);
            try {
                $customer->save();
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }
        $entityTypeId = 'customer';
        $attributes = $this->metadata->getAllAttributesMetadata($entityTypeId);
        foreach ($attributes as $attribute) {
            if ($attribute->isSystem() || !$attribute->isUserDefined()) {
                continue;
            }
            $this->getAttributesArray($attribute, $customer->getDataModel());
        }
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function SaveForGuest() {
        $customerAttr = $this->checkoutSession->getCustomerAttributes();
        if ($customerAttr && !empty($customerAttr)) {
            $customerAttrSession = [];
            foreach ($customerAttr as $attr => $value) {
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                $customerAttrSession[$attr] = $value;
            }
            $entityTypeId = 'customer';
            $attributes = $this->metadata->getAllAttributesMetadata($entityTypeId);
            foreach ($attributes as $attribute) {
                if ($attribute->isSystem() || !$attribute->isUserDefined()) {
                    continue;
                }
                $this->getAttributesArrayForGuest($attribute, $customerAttrSession);
            }
        }
    }

    /**
     * @param \Magento\Customer\Model\Attribute $attribute
     * @param \Magento\Customer\Model\Customer $customer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributesArray($attribute, $customer)
    {
        if ($this->helper->isAttribureForOrderDetail($attribute->getAttributeCode())) {
            $customAttribute = $customer->getCustomAttribute($attribute->getAttributeCode());
            if ($customAttribute != '' && $customAttribute->getValue() != '') {
                $this->attributesArrays[$attribute->getAttributeCode()] = $customAttribute->getValue();
            }
        }
    }

    /**
     * @param \Magento\Customer\Model\Attribute $attribute
     * @param array $customerAttrSession
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributesArrayForGuest($attribute, $customerAttrSession)
    {
        if ($this->helper->isAttribureForOrderDetail($attribute->getAttributeCode())) {
            if (isset($customerAttrSession[$attribute->getAttributeCode()]) && $customerAttrSession[$attribute->getAttributeCode()] != '') {
                $this->attributesArrays[$attribute->getAttributeCode()] = $customerAttrSession[$attribute->getAttributeCode()];
            }
        }
    }
}