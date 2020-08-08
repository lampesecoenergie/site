<?php
namespace Fooman\PdfCustomiser\Model\System;

/**
 * Print Comment setting choices
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class PrintComments implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * 0   Printing of status history and comments is disabled
     * 1   Prints all status history and comments
     * 2   Prints the status history and comments that are frontend visible only
     * 3   Prints the status history and comments that are backend visible only
     */
    const   PRINT_NONE = 0;
    const   PRINT_ALL = 1;
    const   PRINT_FRONTEND_VISIBLE = 2;
    const   PRINT_BACKEND_VISIBLE = 3;

    /**
     * supply dropdown choices for printing of order history with comments
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PRINT_NONE, 'label' => __('No')],
            ['value' => self::PRINT_ALL, 'label' => __('All')],
            ['value' => self::PRINT_FRONTEND_VISIBLE, 'label' => __('Frontend Visible Only')],
            ['value' => self::PRINT_BACKEND_VISIBLE, 'label' => __('Backend Visible Only')]
        ];
    }
}
