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
class Pageorientation implements \Magento\Framework\Data\OptionSourceInterface
{
    const PORTRAIT = 'P';
    const LANDSCAPE = 'L';

    /**
     * supply dropdown choices for page layout
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PORTRAIT, 'label' => __('Portrait')],
            ['value' => self::LANDSCAPE, 'label' => __('Landscape')]
        ];
    }
}
