<?php
namespace Fooman\PdfCore\Block\Pdf\Column;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class TaxPercentage extends \Fooman\PdfCore\Block\Pdf\Column implements \Fooman\PdfCore\Block\Pdf\ColumnInterface
{
    const DEFAULT_WIDTH = 12;
    const DEFAULT_TITLE = 'Tax %';

    public function getGetter()
    {
        return [$this, 'getTaxPercent'];
    }

    public function getTaxPercent($row)
    {
        $orderItem = $this->getOrderItem($row);
        return $this->formatPercentage($orderItem->getTaxPercent());
    }

    public function formatPercentage($input)
    {
        $rate = (float)$input;
        if ($rate == (int)$input) {
            $rate = number_format($rate, 1);
        }
        return sprintf('%s %%', $rate);
    }
}
