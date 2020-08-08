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
 * Class Info
 * @package Ced\EbayMultiAccount\Block\Adminhtml\Profile\Edit\Tab
 */
class Info extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Info constructor.
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
        $profileAccount = $this->_coreRegistry->registry('ebay_account');

        $fieldset = $form->addFieldset('profile_info', array('legend' => __('Profile Information')));

        $fieldset->addField(
            'account_id', 'hidden',
            array(
                'name'  => 'account_id',
                'value' => $profileAccount->getId()
            )
        );

        $fieldset->addField('profile_code', 'text',
            array(
                'name' => "profile_code",
                'label' => __('Profile Code'),
                'note' => __('For internal use. Must be unique with no spaces'),
                'class' => 'validate-code',
                'required' => true,
                'value' => $profile->getData('profile_code'),
            )
        );

        $fieldset->addField('profile_name', 'text',
            array(
                'name' => "profile_name",
                'label' => __('Profile Name'),
                'class' => '',
                'required' => true,
                'value' => $profile->getData('profile_name'),
            )
        );

        $fieldset->addField('account_configuration_id', 'select',
            array(
                'name' => "account_configuration_id",
                'label' => __('Account Configuration'),
                'value' => $profile->getData('account_configuration_id'),
                'required' => true,
                'values' => $this->_objectManager->get('Ced\EbayMultiAccount\Model\Source\AccountConfig\Configuration')->toOptionArray($profileAccount->getAccountLocation()),
            )
        );

        $fieldset->addField('profile_status', 'select',
            array(
                'name' => "profile_status",
                'label' => __('Profile Status'),
                'value' => $profile->getData('profile_status'),
                'values' => $this->_objectManager->get('Ced\EbayMultiAccount\Model\Source\Profile\Status')->getOptionArray(),
            )
        );

        $fieldset->addField('in_profile_product', 'hidden',
            array(
                'name' => 'in_profile_product',
                'id' => 'in_profile_product',
            )
        );

        if ($profile->getId()) {
            $form->getElement('profile_code')->setDisabled(1);
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}