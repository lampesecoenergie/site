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

use Ced\EbayMultiAccount\Helper\Data;
use Magento\Framework\Option\ArrayInterface;

class InternationalShippingService implements ArrayInterface
{
	/**
     * @var Data
     */
    public $dataHelper;

    /**
     * InternationalShippingService Constructor
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
        if (!empty($shippingDetails) && isset($shippingDetails['ShippingServiceDetails'])) {
            $options[] = [
                      'disabled' => 'disabled',
                      'value' => "",
                      'label' => "Please select the option"
                  ];
            foreach ($shippingDetails['ShippingServiceDetails'] as $value) {
                if (isset($value['InternationalService'])) {
                    $options[]=[
                      'value' => $value['ShippingService'],
                      'label' => $value['Description']
                  ];
                }
            }
        }
        return $options;
    }
}