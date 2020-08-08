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

class ServiceType implements \Magento\Framework\Option\ArrayInterface
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
    	return [
                ['value'=>'Flat', 'label'=>'Flat'],
                ['value'=>'Calculated', 'label'=>'Calculated'],
                ['value'=>'CalculatedDomesticFlatInternational', 'label'=>'Calculated Domestic Flat International'],
                ['value'=>'CustomCode', 'label'=>'CustomCode'],
                ['value'=>'FlatDomesticCalculatedInternational', 'label'=>'Flat Domestic Calculated International'],
                ['value'=>'FreightFlat', 'label'=>'Freight Flat'],
                ['value'=>'NotSpecified', 'label' => 'Not Specified']
            ];
   	}
}