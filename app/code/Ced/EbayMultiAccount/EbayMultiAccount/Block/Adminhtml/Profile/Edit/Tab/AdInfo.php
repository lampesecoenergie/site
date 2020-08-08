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

namespace Ced\EbayMultiAccount\Block\Adminhtml\Profile\Edit\Tab;

/**
 * Class AdInfo
 * @package Ced\EbayMultiAccount\Block\Adminhtml\Profile\Edit\Tab
 */
class AdInfo extends \Magento\Backend\Block\Widget\Form\Generic
{
	
	public function __construct(
    		\Magento\Backend\Block\Widget\Context $context,
    		\Magento\Framework\Registry $registry,
    		\Magento\Framework\Data\FormFactory $formFactory,
            \Magento\Framework\ObjectManagerInterface $objectInterface,
    		array $data = []
    ) {
    	$this->_coreRegistry = $registry;
    	$this->_objectManager = $objectInterface;
    	parent::__construct($context,$registry, $formFactory);
    }
	protected function _prepareForm(){
		
		$form=$this->_formFactory->create();
		$profile = $this->_coreRegistry->registry('current_profile');
	  
		$fieldset = $form->addFieldset('advanced_info', array('legend'=>__('Advanced Information')));
	
		$fieldset->addField('identifier_type', 'select',
				array(
						'name'      => "identifier_type",
						'label'     => __('Identifier Type'),
						'note'  	=> __('if you want to select ISBN then please insert it into manufacturer part number field below.'),
						'values'    => $this->_objectManager->get('Ced\EbayMultiAccount\Model\Source\Profile\IdentifierType')->getOptionArray(),
						'value'     => $profile->getData('identifier_type'),
				)
		);

		$fieldset->addField('identifier_val', 'text',
				array(
						'name'      => "identifier_val",
						'label'     => __('Identifier Value'),
						'note'  	=> __('Identifier value, for the selected "Identifier Type" above.'),
						'value'    =>$profile->getData('identifier_val'),
				)
		);

		$fieldset->addField('brand', 'text',
				array(
						'name'      => "brand",
						'label'     => __('Product Brand'),
						'note'  	=> __('Product brand for sending on marketplaces.'),
						'value'    =>$profile->getData('brand'),
				)
		);

		$fieldset->addField('manufacturer', 'text',
				array(
						'name'      => "manufacturer",
						'label'     => __('Product Manufacturer'),
						'note'  	=> __('Manufacturer of the product.'),
						'value'    =>$profile->getData('manufacturer'),
				)
		);

		$fieldset->addField('manufacturer_part_no', 'text',
				array(
						'name'      => "manufacturer_part_no",
						'label'     => __('Manufacturer Part Number'),
						'note'  	=> __('Manufacturer defined unique identifier for an item. An alphanumeric string, max 20 characters including space.'),
						'value'    =>$profile->getData('manufacturer_part_no'),
				)
		);

		$fieldset->addField('packs', 'text',
				array(
						'name'      => "packs",
						'label'     => __('Packs Or Sets'),
						'note'  	=> __('Identify the package count of this product.'),
						'value'    =>$profile->getData('packs'),
				)
		);

		$this->setForm($form);
		return parent::_prepareForm();
	}
	
}