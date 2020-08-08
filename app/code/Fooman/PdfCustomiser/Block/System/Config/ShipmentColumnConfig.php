<?php
namespace Fooman\PdfCustomiser\Block\System\Config;

/**
 * Column configuration, removes columns not applicable on shipments
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ShipmentColumnConfig extends \Fooman\PdfCore\Block\System\Config\ColumnConfig
{
    protected $excludes = [
        'qtyOrdered',
        'subtotal',
        'subtotalExcl',
        'discount',
        'rowtotal',
        'tax'
    ];
}
