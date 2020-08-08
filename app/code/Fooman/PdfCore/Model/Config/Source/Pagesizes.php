<?php
namespace Fooman\PdfCore\Model\Config\Source;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Pagesizes implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * supply dropdown choices for page size
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'A4', 'label' => __('A4')],
            ['value' => 'LETTER', 'label' => __('letter')]
        ];
    }
}
