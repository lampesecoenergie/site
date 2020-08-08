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

namespace Ced\EbayMultiAccount\Block\Adminhtml\AccountConfig\Edit\Tab;

/**
 * Class Configuration
 * @package Ced\EbayMultiAccount\Block\Adminhtml\AccountConfig\Edit\Tab
 */
class Configuration extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Configuration constructor.
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
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $account = $this->_objectManager->get('Ced\EbayMultiAccount\Model\AccountConfig')->load($id);
        } else {
            $account = $this->_objectManager->get('Ced\EbayMultiAccount\Model\AccountConfig');
        }

        $fieldset = $form->addFieldset('accountconfig_info', ['legend' => __('Account Configuration Information')]);
        $location = array();
        $location = $this->_objectManager->get('Ced\EbayMultiAccount\Model\Config\Location')->getOptionArray();
        //$location = array('' => '--Please Select Location--') + $location;
        $fieldset->addField('account_location', 'select',
            [
                'name' => "account_location",
                'label' => __('Select Location'),
                'required' => true,
                'value' => $account->getData('account_location'),
                'values' => $location,
            ]
        );

        $fieldset->addField('accountconfig_completedetail', 'text', [
                'label' => __('Select Account'),
                'class' => 'action',
                'name' => 'accountconfig_completedetail'
            ]
        );

        $locations = $form->getElement('accountconfig_completedetail');
        $locations->setRenderer(
            $this->getLayout()->createBlock('Ced\EbayMultiAccount\Block\Adminhtml\AccountConfig\Edit\Tab\CompleteDetails')
        );


        $fieldset = $form->addFieldset('accountconfig_alldetails', ['legend' => __('eBay Payment Details/ Shipping Details/ Return Policy')]);
        $fieldset->addField('accountconfig_alldetail', 'text', [
                'label' => __('All Details'),
                'class' => 'action',
                'name' => 'accountconfig_alldetail'
            ]
        );

        $locations = $form->getElement('accountconfig_alldetail');
        $locations->setRenderer($this->getLayout()->createBlock('Ced\EbayMultiAccount\Block\Adminhtml\AccountConfig\Edit\Tab\AllDetails')
        );

        $this->setForm($form);
        return parent::_prepareForm();
    }
}