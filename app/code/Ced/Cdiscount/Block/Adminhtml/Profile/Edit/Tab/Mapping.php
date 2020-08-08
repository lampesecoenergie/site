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
 * @category  Ced
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Block\Adminhtml\Profile\Edit\Tab;


class Mapping extends \Magento\Backend\Block\Widget\Form\Generic
{

    public $cdiscount;

    public $product;

    public $config;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        \Ced\Cdiscount\Helper\Config $config,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectInterface;
        $this->config = $config;
        parent::__construct($context, $registry, $formFactory);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        $product = $this->product->create(
            [
            'config' => $this->config->getApiConfig()
            ]
        );

        $fieldset = $form->addFieldset('category', ['legend' => __('Cdiscount Category Listing')]);

        $catgories = $product->getCategories();

        $parentCategories = [];
        foreach ($catgories as $catgory) {
            $parentCategories[] = ['value' => $catgory['categoryId'], 'label' =>  $catgory['name']];
        }

        $fieldset->addField(
            'profile_category_1',
            'select',
            [
                'name' => 'profile_category_1',
                'label' => __('Parent Category'),
                'title' => __('Parent Category'),
                'class' => 'level-category',
                'required' => true,
                'style' => 'width: 100%',
                'values' => $parentCategories,
            ]
        );

        $fieldset->addField(
            'profile_category_2',
            'select',
            [
                'name' => 'profile_category_2',
                'label' => __('Category Level 1'),
                'title' => __('Category Level 1'),
                'class' => 'level-1-category',
                'required' => false,
                'style' => 'width: 100%',
                'values' => []
            ]
        );

        $fieldset->addField(
            'profile_category_3',
            'select',
            [
                'name' => 'profile_category_3',
                'label' => __('Category  Level 2'),
                'title' => __('Category  Level 2'),
                'class' => 'level-2-category',
                'required' => false,
                'style' => 'width: 100%',
                'values' => []
            ]
        );

        $fieldset->addField(
            'profile_category_4',
            'select',
            [
                'name' => 'profile_category_4',
                'label' => __('Category  Level 3'),
                'title' => __('Category  Level 3'),
                'class' => 'level-3-category',
                'required' => false,
                'style' => 'width: 100%',
                'values' => []
            ]
        );

        $fieldset->addField(
            'profile_category_5',
            'select',
            [
                'name' => 'profile_category_5',
                'label' => __('Category  Level 4'),
                'title' => __('Category  Level 4'),
                'class' => 'level-4-category',
                'required' => false,
                'style' => 'width: 100%',
                'values' => []
            ]
        );

        $fieldset->addField(
            'profile_category_6',
            'select',
            [
                'name' => 'profile_category_6',
                'label' => __('Category  Level 5'),
                'title' => __('Category  Level 5'),
                'class' => 'level-4-category',
                'required' => false,
                'style' => 'width: 100%',
                'values' => []
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
            $this->getLayout()->createBlock('Ced\Cdiscount\Block\Adminhtml\Profile\Edit\Tab\Attribute\CategoryJs')
        );

        $fieldset = $form->addFieldset(
            'attributes_fieldset',
            [
                'legend' => __('Cdiscount Attributes Mapping')
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
                'Ced\Cdiscount\Block\Adminhtml\Profile\Edit\Tab\Attribute\Attributes',
                'cdiscount_attributes'
            )
        );

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
