<?php
namespace Fooman\PdfCustomiser\Model\System;

/**
 * Integrated labels print choices
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class IntegratedLabelsOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * supply dropdown choices for integrated label content
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value'=> '0',
                'label'=> __('Don\'t Use')
            ],
            [
                'value'=> 'singleshipping',
                'label'=> __('Single - Shipping Address Label')
            ],
            [
                'value'=> 'singlebilling',
                'label'=> __('Single - Billing Address Label')
            ],
            [
                'value'=> 'double',
                'label'=> __('Double - Both Addresses')
            ],
            [
                'value'=> 'shipping-giftmessage',
                'label'=> __('Double - Shipping Address and Gift Message')
            ]
        ];
    }
}
