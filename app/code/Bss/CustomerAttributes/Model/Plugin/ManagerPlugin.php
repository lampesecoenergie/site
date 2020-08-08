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
namespace Bss\CustomerAttributes\Model\Plugin;

/**
 * Class ManagerPlugin
 *
 * @package Bss\CustomerAttributes\Model\Plugin
 */
class ManagerPlugin
{
    /**
     * @param \Magento\Customer\Model\Attribute $subject
     * @param $result
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return string
     */
    public function afterGetDataUsingMethod(
        \Magento\Customer\Model\Attribute $subject,
        $result
    ) {
        if ($result == 'radio') {
            return 'select';
        } elseif ($result == 'checkboxs') {
            return 'multiselect';
        } else {
            return $result;
        }
    }

    /**
     * Check whether attribute is filterable in admin grid and it is allowed
     *
     * @param \Magento\Customer\Model\Attribute $subject
     * @param string $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCanBeFilterableInGrid(\Magento\Customer\Model\Attribute $subject, $result)
    {
        return $result || ($subject->getData('is_filterable_in_grid') && in_array($subject->getFrontendInput(), [
            'radio', 'checkboxs']));
    }
}
