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

use Magento\Framework\Option\ArrayInterface;
use Ced\EbayMultiAccount\Helper\Data;

class ShippingCarrier implements ArrayInterface
{
	/**
     * @var Data
     */
    public $dataHelper;

    /**
     * ShippingCarrier Constructor
     * @param Data $dataHelper
     */
    public function __construct(
        Data $dataHelper
    )
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $shippingDetails = $this->dataHelper->getShhipingDetails();
        if (isset($shippingDetails['ShippingCarrierDetails'])) {
            foreach ($shippingDetails['ShippingCarrierDetails'] as $value) {
                if (isset($value['ShippingCarrier'])) {
                    $options [] = [
                        'value' => $value['ShippingCarrier'],
                        'label' => $value['ShippingCarrier']
                    ];
                }
            }
        }
        return $options;
    }
}