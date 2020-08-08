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
 * Config source for end of line character.
 *
 * @author Smile (http://www.smile.fr)
 */
class EndOfLineCharacter implements ArrayInterface
{
    /**
     * LF character.
     */
    const LF = 'lf';

    /**
     * CR character.
     */
    const CR = 'cr';

    /**
     * CR+LF characters.
     */
    const CRLF = 'crlf';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::LF, 'label' => __('LF')],
            ['value' => self::CR, 'label' => __('CR')],
            ['value' => self::CRLF, 'label' => __('CR+LF')],
        ];
    }
}
