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
namespace Bss\CustomerAttributes\Ui\Component\Listing;

/**
 * Class Columns
 *
 * @package Bss\CustomerAttributes\Ui\Component\Listing
 */
class Columns extends \Magento\Customer\Ui\Component\Listing\Columns
{
    /**
     * @var array
     */
    protected $filterMapCustomerAttr = [
        'radio' => 'select',
        'checkboxs' => 'select',
    ];

    /**
     * Retrieve filter type by $frontendInput
     *
     * @param string $frontendInput
     * @return string
     */
    protected function getFilterType($frontendInput)
    {
        if (isset($this->filterMapCustomerAttr[$frontendInput])) {
            return $this->filterMapCustomerAttr[$frontendInput];
        }
        return parent::getFilterType($frontendInput);
    }
}
