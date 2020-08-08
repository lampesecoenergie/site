<?php
namespace Fooman\PdfCustomiser\Model\Pdf\Total;

/**
 * Overriding core Magento here as prior to 2.2.8 canDisplay() was missing
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Weee extends \Magento\Weee\Model\Sales\Pdf\Weee
{
    public function canDisplay()
    {
        $amount = $this->getRealWeeeAmount();
        return $this->getDisplayZero() === 'true' || $amount != 0;
    }

    private function getRealWeeeAmount()
    {
        $items = $this->getSource()->getAllItems();
        $store = $this->getSource()->getStore();

        return $this->_weeeData->getTotalAmounts($items, $store);
    }
}
