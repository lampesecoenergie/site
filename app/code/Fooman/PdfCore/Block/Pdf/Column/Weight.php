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
class Weight extends \Fooman\PdfCore\Block\Pdf\Column implements \Fooman\PdfCore\Block\Pdf\ColumnInterface
{
    const DEFAULT_WIDTH = 18;
    const DEFAULT_TITLE = 'Weight';

    public function getGetter()
    {
        return [$this, 'getWeight'];
    }

    public function getWeight($row)
    {
        $orderItem = $this->getOrderItem($row);
        $unit = $this->_scopeConfig->getValue(
            'general/locale/weight_unit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return sprintf('%s %s', (float)$orderItem->getRowWeight(), $unit);
    }
}
