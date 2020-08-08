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
class QtyDetails extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
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
        return $this->escapeHtml(parent::_getValue($row), ['br','b']);
    }
}
