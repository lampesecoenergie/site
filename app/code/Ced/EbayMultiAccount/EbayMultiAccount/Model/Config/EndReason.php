<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_EbayMultiAccount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\EbayMultiAccount\Model\Config;

class EndReason implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' =>'Incorrect','value' => 'Incorrect'],
            ['label' =>'LostOrBroken','value' => 'LostOrBroken'],
            ['label' =>'NotAvailable','value' => 'NotAvailable'],
            ['label' =>'OtherListingError','value' => 'OtherListingError'],
            ['label' =>'SellToHighBidder','value' => 'SellToHighBidder'],
            ['label' =>'Sold','value' => 'Sold']
        ];
    }    
}