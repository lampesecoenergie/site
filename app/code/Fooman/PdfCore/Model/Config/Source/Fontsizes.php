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
class Fontsizes implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * supply dropdown choices for fontsizes
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '8', 'label' => 8],
            ['value' => '9.5', 'label' => 9.5],
            ['value' => '10', 'label' => 10],
            ['value' => '10.5', 'label' => 10.5],
            ['value' => '11', 'label' => 11],
            ['value' => '12', 'label' => 12],
            ['value' => '14', 'label' => 14],
            ['value' => '16', 'label' => 16],
            ['value' => '18', 'label' => 18],
            ['value' => '20', 'label' => 20]
        ];
    }
}
