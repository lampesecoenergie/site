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
namespace Bss\CustomerAttributes\Ui\Component;

/**
 * Class ColumnFactory
 *
 * @package Bss\CustomerAttributes\Ui\Component
 */
class ColumnFactory extends \Magento\Customer\Ui\Component\ColumnFactory
{
    /**
     * @var array
     */
    protected $dataTypeMapCustomerAttr = [
        'radio' => 'select',
        'checkboxs' => 'select',
    ];

    /**
     * @param string $frontendType
     * @return mixed|string
     */
    protected function getDataType($frontendType)
    {
        if (isset($this->dataTypeMapCustomerAttr[$frontendType])) {
            return $this->dataTypeMapCustomerAttr[$frontendType];
        }
        return parent::getDataType($frontendType);
    }
}
