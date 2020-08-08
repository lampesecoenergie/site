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
namespace Bss\CustomerAttributes\Model\Metadata\Form;

use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Class Checkboxs
 *
 * @package Bss\CustomerAttributes\Model\Metadata\Form
 */
class Checkboxs extends \Bss\CustomerAttributes\Model\Metadata\Form\Radio
{
    /**
     * {@inheritdoc}
     */
    public function extractValue(RequestInterface $request)
    {
        $values = $this->_getRequestValue($request);
        if ($values !== false && !is_array($values)) {
            $values = [$values];
        }
        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function compactValue($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = parent::compactValue($val);
            }

            $value = implode(',', $value);
        }
        return parent::compactValue($value);
    }

    /**
     * {@inheritdoc}
     */
    public function outputValue($format = ElementFactory::OUTPUT_FORMAT_TEXT)
    {
        $values = $this->_value;
        if (!is_array($values)) {
            $values = explode(',', $values);
        }

        if (ElementFactory::OUTPUT_FORMAT_ARRAY === $format || ElementFactory::OUTPUT_FORMAT_JSON === $format) {
            return $values;
        }

        $output = [];
        foreach ($values as $value) {
            if (!$value) {
                continue;
            }
            $output[] = $this->_getOptionText($value);
        }

        $output = implode(', ', $output);

        return $output;
    }
}
