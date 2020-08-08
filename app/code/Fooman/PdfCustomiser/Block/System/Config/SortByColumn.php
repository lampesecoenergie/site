<?php
namespace Fooman\PdfCustomiser\Block\System\Config;

use Fooman\PdfCore\Block\System\Config\Columns;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Add default column sort order to all available columns
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class SortByColumn extends Columns implements OptionSourceInterface
{

    public function toOptionArray()
    {
        $choices = [];
        $choices[] = ['value' => 0, 'label' => __('Default')];
        return array_merge_recursive($choices, $this->getColumns());
    }
}
