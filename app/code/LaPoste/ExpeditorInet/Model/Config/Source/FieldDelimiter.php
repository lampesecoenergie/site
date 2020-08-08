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
 * Config source for field delimiter.
 *
 * @author Smile (http://www.smile.fr)
 */
class FieldDelimiter implements ArrayInterface
{
    /**
     * Semicolumn separator.
     */
    const SEMICOLUMN = 'semicolumn';

    /**
     * Comma separator.
     */
    const COMMA = 'comma';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::SEMICOLUMN, 'label' => __('Semicolumn')],
            ['value' => self::COMMA, 'label' => __('Comma')],
        ];
    }
}
