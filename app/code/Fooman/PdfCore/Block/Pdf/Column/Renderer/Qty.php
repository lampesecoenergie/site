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
class Qty extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    const XML_PATH_QTY_AS_INT = 'sales_pdf/all/allqtyasint';

    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return mixed
     */
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore -- Magento 2 Core use
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        $qty = parent::_getValue($row);
        if ($qty && $this->_scopeConfig->getValue(
            self::XML_PATH_QTY_AS_INT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $row->getStoreId()
        )
        ) {
            $qty = (int)$qty;
        }
        if ($qty > 1) {
            $qty = '<b>' . $qty . '</b>';
        }
        return $this->escapeHtml($qty, ['b']);
    }
}
