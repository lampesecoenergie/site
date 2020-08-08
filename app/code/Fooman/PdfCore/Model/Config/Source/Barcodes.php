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
class Barcodes implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * supply dropdown choices for types of barcodes
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'C39', 'label' => __('CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9.')],
            ['value' => 'C39+', 'label' => __('CODE 39 with checksum')],
            ['value' => 'C39E', 'label' => __('CODE 39 EXTENDED')],
            ['value' => 'C39E+', 'label' => __('CODE 39 EXTENDED + CHECKSUM')],
            ['value' => 'S25', 'label' => __('Standard 2 of 5')],
            ['value' => 'S25+', 'label' => __('Standard 2 of 5 + CHECKSUM')],
            ['value' => 'I25', 'label' => __('Interleaved 2 of 5')],
            ['value' => 'I25+', 'label' => __('Interleaved 2 of 5 + CHECKSUM')],
            ['value' => 'C128', 'label' => __('CODE 128')],
            ['value' => 'C128A', 'label' => __('CODE 128 A')],
            ['value' => 'C128B', 'label' => __('CODE 128 B')],
            ['value' => 'C128C', 'label' => __('CODE 128 C')],
            ['value' => 'EAN2', 'label' => __('EAN 2')],
            ['value' => 'EAN5', 'label' => __('EAN 5')],
            ['value' => 'EAN8', 'label' => __('EAN 8')],
            ['value' => 'EAN13', 'label' => __('EAN 13')],
            ['value' => 'UPCA', 'label' => __('UPC-A')],
            ['value' => 'UPCE', 'label' => __('UPC-E')],
            ['value' => 'MSI', 'label' => __('MSI (Variation of Plessey code)')],
            ['value' => 'MSI+', 'label' => __('MSI + CHECKSUM (modulo 11)')],
            ['value' => 'POSTNET', 'label' => __('POSTNET')],
            ['value' => 'PLANET', 'label' => __('PLANET')],
            ['value' => 'RMS4CC', 'label' => __('RMS4CC (Royal Mail 4-state Customer Code) - CBC (Customer Bar Code)')],
            ['value' => 'KIX', 'label' => __('KIX (Klant index - Customer index)')],
            ['value' => 'IMB', 'label' => __('IMB - Intelligent Mail Barcode - Onecode - USPS-B-3200')],
            ['value' => 'CODABAR', 'label' => __('CODABAR')],
            ['value' => 'CODE11', 'label' => __('CODE 11')],
            ['value' => 'PHARMA', 'label' => __('PHARMACODE')]
        ];
    }
}
