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
namespace Bss\CustomerAttributes\Plugin\Model;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Type;

/**
 * Class AttributeMetadataResolver
 * @package Bss\CustomerAttributes\Plugin\Model
 */
class AttributeMetadataResolver
{
    /**
     * @param \Magento\Customer\Model\AttributeMetadataResolver $subject
     * @param array $meta
     * @param AbstractAttribute $attribute
     * @param Type $entityType
     * @param bool $allowToShowHiddenAttributes
     * @return array
     */
    public function afterGetAttributesMeta(
        \Magento\Customer\Model\AttributeMetadataResolver $subject,
        $meta,
        AbstractAttribute $attribute,
        Type $entityType,
        $allowToShowHiddenAttributes
    ) {
        $usedInForms = $attribute->getUsedInForms();
        if (in_array('is_customer_attribute', $usedInForms)
            && $attribute->getFrontendInput() == 'file'
        ) {
            if (isset($meta['arguments']['data']['config']['validation']['max_file_size'])) {
                $fileSize = $meta['arguments']['data']['config']['validation']['max_file_size'] * 1000;
                $meta['arguments']['data']['config']['maxFileSize'] = $fileSize;
                $meta['arguments']['data']['config']['validation']['max_file_size'] = $fileSize;
            }
        }
        if (in_array('is_customer_attribute', $usedInForms)
            && !$attribute->getIsVisible()
        ) {
            if (isset($meta['arguments']['data']['config'])) {
                $meta['arguments']['data']['config']['visible'] = $attribute->getIsVisible();
            }
        }
        return $meta;
    }
}
