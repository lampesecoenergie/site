<?php
namespace Fooman\PdfCore\Block\Pdf;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
interface ColumnInterface
{
    public function getTitle();

    public function getWidthAbs();

    public function getType();
}
