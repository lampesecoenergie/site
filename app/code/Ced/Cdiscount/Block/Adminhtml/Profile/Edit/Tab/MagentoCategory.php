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

use Magento\Backend\Block\Widget\Form\Generic;

class MagentoCategory extends Generic
{
    public $objectManager;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->objectManager = $objectInterface;
        parent::__construct($context, $registry, $formFactory);
    }

    protected function _prepareForm()
    {

        $form = $this->_formFactory->create();
        $profile = $this->_coreRegistry->registry('current_profile');

        $fieldset = $form->addFieldset('advanced_info', ['legend' => __('Magento Category Mapping')]);

        $fieldset->addField(
            'magento_category',
            'select',
            [
                'name' => "magento_category",
                'label' => __('Select Category'),
                'values' => $this->objectManager->get('Ced\Cdiscount\Model\Source\Profile\MagentoCategoryMapping')
                    ->toOptionArray(),
                'style' => 'width: 100%',
                'required' => true,
                'value' => $profile->getData('magento_category'),
            ]
        );
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
