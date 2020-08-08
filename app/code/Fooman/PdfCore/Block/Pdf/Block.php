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
class Block extends PdfAbstract
{
    // phpcs:ignore PSR2.Classes.PropertyDeclaration -- Magento 2 Core use
    protected $_template = 'Fooman_PdfCore::pdf/block.phtml';

    protected $content;

    /**
     * @param $content
     *
     * @return mixed
     */
    public function setContent($content)
    {
        return $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }
}
