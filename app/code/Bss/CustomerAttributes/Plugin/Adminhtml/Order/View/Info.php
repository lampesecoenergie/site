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
 * @copyright  Copyright (c) 2015-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomerAttributes\Plugin\Adminhtml\Order\View;

use Magento\Eav\Model\AttributeDataFactory;

class Info
{
    /**
     * @var \Bss\CustomerAttributes\Helper\GetHtmltoEmail
     */
    protected $getHtmltoEmail;

    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    protected $helper;

    /**
     * @var \Magento\Customer\Api\CustomerMetadataInterface
     */
    protected $metadata;

    /**
     * @var \Magento\Customer\Model\Metadata\ElementFactory
     */
    protected $elementFactory;

    /**
     * Info constructor.
     * @param \Magento\Customer\Api\CustomerMetadataInterface $metadata
     * @param \Magento\Customer\Model\Metadata\ElementFactory $elementFactory
     * @param \Bss\CustomerAttributes\Helper\GetHtmltoEmail $getHtmltoEmail
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $customerattribute
     */
    public function __construct(
        \Bss\CustomerAttributes\Helper\GetHtmltoEmail $getHtmltoEmail,
        \Bss\CustomerAttributes\Helper\Customerattribute $customerattribute,
        \Magento\Customer\Api\CustomerMetadataInterface $metadata,
        \Magento\Customer\Model\Metadata\ElementFactory $elementFactory
    ) {
        $this->getHtmltoEmail = $getHtmltoEmail;
        $this->helper = $customerattribute;
        $this->metadata = $metadata;
        $this->elementFactory = $elementFactory;
    }

    /**
     * Around Get Customer Account Data
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\View\Info $subject
     * @param callable $proceed
     * @return array
     */
    public function aroundGetCustomerAccountData(
        \Magento\Sales\Block\Adminhtml\Order\View\Info $subject,
        \Closure $proceed
    ) {
        $accountData = [];
        $entityType = 'customer';
        if ($this->getHtmltoEmail->getConfig('bss_customer_attribute/general/enable')) {
            /* @var \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute */
            foreach ($this->metadata->getAllAttributesMetadata($entityType) as $attribute) {
                $displayOrderDetail = (bool)$this->helper->isAttribureForOrderDetail($attribute->getAttributeCode());
                if (!$attribute->isVisible() || $attribute->isSystem() || !$displayOrderDetail) {
                    continue;
                }
                $orderKey = sprintf('customer_%s', $attribute->getAttributeCode());
                $orderValue = $subject->getOrder()->getData($orderKey);
                if ($orderValue != '') {
                    $metadataElement = $this->elementFactory->create($attribute, $orderValue, $entityType);
                    $value = $metadataElement->outputValue(AttributeDataFactory::OUTPUT_FORMAT_HTML);
                    $sortOrder = $attribute->getSortOrder() + $attribute->isUserDefined() ? 200 : 0;
                    $sortOrder = $this->prepareAccountDataSortOrder($accountData, $sortOrder);
                    if ($attribute->getFrontendInput() =='file') {
                        $orderValue = substr($orderValue, 1);
                        $accountData[$sortOrder] = [
                            'label' => $attribute->getFrontendLabel(),
                            'value' => '<a href="'
                                .$subject->escapeUrl($this->getHtmltoEmail->getViewFile($orderValue)).'">'
                                . $this->getFileName($subject->escapeHtml($orderValue)) . '</a>',
                            ];
                    } else {
                        $accountData[$sortOrder] = [
                            'label' => $attribute->getFrontendLabel(),
                            'value' => $subject->escapeHtml($value, ['br']),
                        ];
                    }
                }
            }
            ksort($accountData, SORT_NUMERIC);

            return $accountData;
        }
        return $proceed();
    }

    /**
     * Get File Name
     *
     * @param string $filename
     * @return string
     */
    protected function getFileName($filename)
    {
        if (strpos($filename, "/") !== false) {
            $nameArr = explode("/", $filename);
            return end($nameArr);
        }
        return $filename;
    }

    /**
     * Find sort order for account data
     * Sort Order used as array key
     *
     * @param array $data
     * @param int $sortOrder
     * @return int
     */
    protected function prepareAccountDataSortOrder(array $data, $sortOrder)
    {
        if (isset($data[$sortOrder])) {
            return $this->prepareAccountDataSortOrder($data, $sortOrder + 1);
        }

        return $sortOrder;
    }
}
