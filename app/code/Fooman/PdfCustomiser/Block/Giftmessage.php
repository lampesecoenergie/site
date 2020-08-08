<?php
namespace Fooman\PdfCustomiser\Block;

/**
 * Block to display Magento giftmessages on order level
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Giftmessage extends \Fooman\PdfCore\Block\Pdf\Block
{
    // phpcs:ignore PSR2.Classes.PropertyDeclaration
    protected $_template = 'Fooman_PdfCustomiser::pdf/giftmessage.phtml';

    /**
     * @return \Magento\GiftMessage\Api\Data\MessageInterface
     */
    public function getGiftmessage()
    {
        return $this->getData('giftmessage');
    }
}
