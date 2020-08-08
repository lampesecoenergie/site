<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module
 * to newer versions in the future.
 *
 * @copyright 2017 La Poste
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace LaPoste\ExpeditorInet\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Config source for field enclosure.
 *
 * @author Smile (http://www.smile.fr)
 */
class FieldEnclosure implements ArrayInterface
{
    /**
     * Simple quotes delimiter.
     */
    const SIMPLE_QUOTES = 'simple_quotes';

    /**
     * Double quotes delimiter.
     */
    const DOUBLE_QUOTES = 'double_quotes';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::SIMPLE_QUOTES, 'label' => __('Simple Quotes')],
            ['value' => self::DOUBLE_QUOTES, 'label' => __('Double Quotes')],
        ];
    }
}
