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

class ShippingMethods implements \Magento\Framework\Option\ArrayInterface
{
    protected $shipconfig;

    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\Config $shipconfig
    ) {
        $this->shipconfig=$shipconfig;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $methods = [];
        $activeCarriers = $this->shipconfig->getActiveCarriers();
            foreach($activeCarriers as $carrierCode => $carrierModel) {
               $options = [];
                if( $carrierMethods = $carrierModel->getAllowedMethods() ) {
                    foreach ($carrierMethods as $methodCode => $method) {
                        $code= $carrierCode.'_'.$methodCode;
                        $options=['value'=>$code,'label'=>$method];
                    }
               }
                $methods[] = $options;
            }
        return $methods;
    }
}