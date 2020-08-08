<?php
namespace Fooman\PdfCore\Block\Pdf\Column\Renderer;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class CurrencyWithText extends Currency
{

    public function render(\Magento\Framework\DataObject $row)
    {
        $value = $this->_getValue($row);
        if (is_array($value)) {
            $output = [];
            foreach ($value as $price) {
                if ($price['amount'] !== null) {
                    if (isset($price['text'])) {
                        $output[] = $this->insertAmountIntoPlaceholder(
                            $price['text'],
                            $this->formatCurrency($price['amount'], $price['currency'])
                        );
                    } else {
                        $output[] = $this->formatCurrency($price['amount'], $price['currency']);
                    }
                }
            }
            return implode('<br/>', $output);
        }

        if ($value) {
            return $this->formatCurrency($value, $this->_getCurrencyCode($row));
        }

        return $this->getColumn()->getDefault();
    }

    public function insertAmountIntoPlaceholder($text, $formattedCurrency)
    {
        return $this->escapeHtml(sprintf($text, $formattedCurrency), ['br']);
    }
}
