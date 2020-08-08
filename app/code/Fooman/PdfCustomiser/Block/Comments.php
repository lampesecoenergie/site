<?php
namespace Fooman\PdfCustomiser\Block;

/**
 * Block to render comments
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Comments extends \Fooman\PdfCore\Block\Pdf\Block
{
    // phpcs:ignore PSR2.Classes.PropertyDeclaration
    protected $_template = 'Fooman_PdfCustomiser::pdf/comments.phtml';

    public function escapeComment($comment)
    {
        $comment = str_replace('</br>', '<br/>', $comment);
        $comment = $this->escapeHtml($comment, ['br', 'b', 'i']);
        return $comment;
    }
}
