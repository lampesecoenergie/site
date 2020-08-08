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
 * Config source for file charset.
 *
 * @author Smile (http://www.smile.fr)
 */
class FileCharset implements ArrayInterface
{
    /**
     * ISO-8859-1 encoding.
     */
    const ISO88591 = 'ISO-8859-1';

    /**
     * UTF-8 encoding.
     */
    const UTF8 = 'UTF-8';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ISO88591, 'label' => __('ISO-8859-1')],
            ['value' => self::UTF8, 'label' => __('UTF-8')],
        ];
    }
}
