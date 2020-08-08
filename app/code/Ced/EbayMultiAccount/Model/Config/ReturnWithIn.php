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

class ReturnWithIn implements \Magento\Framework\Option\ArrayInterface
{
	/**
     * Objet Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Constructor
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
    	$restocking = $this->objectManager->get('Ced\EbayMultiAccount\Helper\Data')->returnPolicyValue();
        $options = [];
        if (isset($restocking['ReturnPolicyDetails']['ReturnsWithin'])) {
            foreach ($restocking['ReturnPolicyDetails']['ReturnsWithin'] as $value) {
                //if ($value['ReturnsWithinOption'] !== 'Days_14') {
                    $options[]=[
                        'value'=>$value['ReturnsWithinOption'],
                        'label'=>$value['Description']
                    ];
                //}
            }
        }
        return $options;
   	}
}