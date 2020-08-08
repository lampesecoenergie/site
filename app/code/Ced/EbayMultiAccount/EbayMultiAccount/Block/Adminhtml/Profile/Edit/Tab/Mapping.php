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
 * Class Mapping
 * @package Ced\EbayMultiAccount\Block\Adminhtml\Profile\Edit\Tab
 */
class Mapping extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Mapping constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectInterface
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectInterface;
        parent::__construct($context, $registry, $formFactory);
    }

    /**
     * @return $this
     */
    protected function _prepareForm()
    {

        $form = $this->_formFactory->create();
        $profile = $this->_coreRegistry->registry('current_profile');

        $fieldset = $form->addFieldset('category', array('legend' => __('Category Mapping')));

        $fieldset->addField(
            'level_0',
            'select',
            [
                'name' => 'level_0',
                'label' => __('Root Level Category'),
                'title' => __('Root Level Category'),
                'required' => true,
                'values' => $this->_objectManager->create('Ced\EbayMultiAccount\Model\Source\Profile\Category\Rootlevel')->toOptionArray()
            ]
        );

        $fieldset->addField(
            'level_1',
            'select',
            [
                'name' => 'level_1',
                'label' => __('Level 1 Category'),
                'title' => __('Level 1 Category'),
                'required' => true,
                'values' => ""
            ]
        );

        $fieldset->addField(
            'level_2',
            'select',
            [
                'name' => 'level_2',
                'label' => __('Level 2 Category'),
                'title' => __('Level 2 Category'),
                'required' => true,
                'values' => ""
            ]
        );

        $fieldset->addField(
            'level_3',
            'select',
            [
                'name' => 'level_3',
                'label' => __('Level 3 Category'),
                'title' => __('Level 3 Category'),
                'required' => true,
                'values' => ""
            ]
        );

        $fieldset->addField(
            'level_4',
            'select',
            [
                'name' => 'level_4',
                'label' => __('Level 4 Category'),
                'title' => __('Level 4 Category'),
                'required' => true,
                'values' => ""
            ]
        );

        $fieldset->addField(
            'level_5',
            'select',
            [
                'name' => 'level_5',
                'label' => __('Level 5 Category'),
                'title' => __('Level 5 Category'),
                'required' => true,
                'values' => ""
            ]
        );

        $fieldset->addField('category_js', 'text', [
                'label' => __('Category JS Mapping'),
                'class' => 'action',
                'name' => 'category_js_mapping'
            ]
        );

        $locations = $form->getElement('category_js');
        $locations->setRenderer(
            $this->getLayout()->createBlock('Ced\EbayMultiAccount\Block\Adminhtml\Profile\Edit\Tab\Attribute\CategoryJs')
        );

        $fieldset->addField(
            'automplete-2',
            'text',
            [
                'name' => 'automplete-2',
                'label' => __('Search Root Category'),
                'title' => __('Search Root Category'),
                'class' => __('automplete-2')
            ]
        );
        $fieldset->addField('search_category', 'text', [
                'label' => __('Search Category'),
                'class' => 'action',
                'name' => 'search_category'
            ]
        );
        $locations = $form->getElement('search_category');
        $locations->setRenderer(
            $this->getLayout()->createBlock('Ced\EbayMultiAccount\Block\Adminhtml\Profile\Edit\Tab\Search\Searchcategory')
        );

        $fieldset = $form->addFieldset('ebaymultiaccount_attributes', array('legend' => __('Ebay-Magento Category Dependent Attributes Mapping')));

        $fieldset->addField('ebaymultiaccount_attribute', 'text', [
                'label' => __('Attribute Mapping'),
                'class' => 'action',
                'name' => 'required_attribute'
            ]
        );

        $locations = $form->getElement('ebaymultiaccount_attribute');
        $locations->setRenderer($this->getLayout()->createBlock('Ced\EbayMultiAccount\Block\Adminhtml\Profile\Edit\Tab\Attribute\EbayMultiAccountattribute')
        );

        $fieldset = $form->addFieldset('required_attributes', array('legend' => __('Ebay-Magento Required Attributes Mapping')));

        $fieldset->addField('required_attribute', 'text', [
                'label' => __('Attribute Mapping'),
                'class' => 'action',
                'name' => 'required_attribute'
            ]
        );

        $locations = $form->getElement('required_attribute');
        $locations->setRenderer($this->getLayout()->createBlock('Ced\EbayMultiAccount\Block\Adminhtml\Profile\Edit\Tab\Attribute\Requiredattribute')
        );

        $this->setForm($form);
        return parent::_prepareForm();
    }
}