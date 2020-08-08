<?php
namespace Fooman\PdfCustomiser\Block\Table;

use \Magento\Framework\Exception\NoSuchEntityException;

/**
 * Block to output all extras on an item like giftmessages
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Extras extends \Fooman\PdfCore\Block\Pdf\Block
{
    // phpcs:ignore PSR2.Classes.PropertyDeclaration
    protected $_template = 'Fooman_PdfCustomiser::pdf/table/extras.phtml';

    private $orderItemRepository;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\GiftMessage\Api\OrderItemRepositoryInterface $orderItemRepository,
        array $data = []
    ) {
        $this->orderItemRepository = $orderItemRepository;
        parent::__construct($context, $data);
    }

    public function getGiftMessage($item)
    {
        try {
            $giftMessage = $this->orderItemRepository->get($item->getOrderId(), $item->getItemId());
        } catch (NoSuchEntityException $e) {
            $giftMessage = false;
        }
        return $giftMessage;
    }
}
