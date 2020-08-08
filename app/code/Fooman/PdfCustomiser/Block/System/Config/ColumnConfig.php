<?php
namespace Fooman\PdfCustomiser\Block\System\Config;

/**
 * Column configuration, removes columns not applicable on invoices and creditmemos
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ColumnConfig extends \Fooman\PdfCore\Block\System\Config\ColumnConfig
{
    protected $excludes = ['qtyOrdered'];
}
