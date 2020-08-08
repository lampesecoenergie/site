<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Block\Adminhtml\Profile\Edit\Tab;

class Mapping extends \Magento\Backend\Block\Widget\Form\Generic
{
    //protected $_formFactory;

    public $amazon;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        \Amazon\Sdk\Product\Category\Collection $amazonCategories,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectInterface;
        $this->amazon = $amazonCategories;
        parent::__construct($context, $registry, $formFactory);
    }

    protected function _prepareForm()
    {

        $form = $this->_formFactory->create();
        $profile = $this->_coreRegistry->registry('current_profile');

        $fieldset = $form->addFieldset('category', ['legend' => __('Amazon Category Listing')]);

        $fieldset->addField(
            'profile_category',
            'select',
            [
                'name' => 'profile_category',
                'label' => __('Category'),
                'title' => __('Category'),
                'required' => true,
                'style' => 'width: 100%',
                'values' => $this->amazon->getCategories(0),
            ]
        );

        $fieldset->addField(
            'profile_sub_category',
            'select',
            [
                'name' => 'profile_sub_category',
                'label' => __('Sub Category'),
                'title' => __('Sub Category'),
                'style' => 'width: 100%',
                'required' => true,
                'values' => $this->amazon->getCategories(1),
            ]
        );

        $fieldset->addField(
            'category_js',
            'text',
            [
                'label' => __('Category JS Mapping'),
                'class' => 'action',
                'name' => 'category_js_mapping'
            ]
        );

        $locations = $form->getElement('category_js');
        $locations->setRenderer(
            $this->getLayout()
                ->createBlock(\Ced\Amazon\Block\Adminhtml\Profile\Edit\Tab\Attribute\CategoryJs::class)
        );

        $fieldset = $form->addFieldset(
            'attributes_fieldset',
            [
                'legend' => __('Amazon Attributes Mapping')
            ]
        );

        $fieldset->addField(
            'attributes',
            'text',
            [
                'label' => __('Attribute Mapping'),
                'class' => 'action',
                'name' => 'required_attribute'
            ]
        );

        $locations = $form->getElement('attributes');
        $locations->setRenderer(
            $this->getLayout()->createBlock(
                'Ced\Amazon\Block\Adminhtml\Profile\Edit\Tab\Attribute\Attributes',
                'amazon_attributes'
            )
        );

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
