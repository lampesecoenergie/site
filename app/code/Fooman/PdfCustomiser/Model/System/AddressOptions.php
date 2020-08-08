<?php
namespace Fooman\PdfCustomiser\Model\System;

/**
 * Address display choices
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class AddressOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    const BOTH_ADDRESSES = 'both';
    const SHIPPING_ONLY = 'shipping';
    const BILLING_ONLY = 'billing';

    /**
     * supply dropdown options for address choices
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::BILLING_ONLY, 'label' => __('Billing Address only')],
            ['value' => self::SHIPPING_ONLY, 'label' => __('Shipping Address only')],
            ['value' => self::BOTH_ADDRESSES, 'label' => __('Both Addresses')]
        ];
    }
}
