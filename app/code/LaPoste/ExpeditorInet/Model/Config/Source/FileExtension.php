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
 * Config source for file extension.
 *
 * @author Smile (http://www.smile.fr)
 */
class FileExtension implements ArrayInterface
{
    /**
     * Text file extension.
     */
    const TXT = 'txt';

    /**
     * CSV file extension.
     */
    const CSV = 'csv';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::TXT, 'label' => __('.txt')],
            ['value' => self::CSV, 'label' => __('.csv')],
        ];
    }
}
