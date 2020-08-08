<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fooman\PdfCustomiser\Helper;

/**
 * Provide access to the totals
 */
class Totals extends \Magento\Sales\Model\Order\Pdf\AbstractPdf
{
    /**
     * not required but "implemented" due to abstract class requirement
     */
    // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
    public function getPdf()
    {
        //noop
    }

    /**
     * make the totals list accessible
     *
     * @return \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal[]
     */
    public function getTotalsList()
    {
        return $this->_getTotalsList();
    }
}
