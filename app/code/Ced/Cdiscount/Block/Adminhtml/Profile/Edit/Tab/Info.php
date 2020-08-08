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

class Info extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected  $_store;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $store,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectInterface;
        $this->_store = $store;
        parent::__construct($context,$registry, $formFactory);
    }
    protected function _prepareForm(){

        $form=$this->_formFactory->create();
        //$form = $this->getForm();

        //$form = new Varien_Data_Form();

        $profile = $this->_coreRegistry->registry('current_profile');

        $fieldset = $form->addFieldset('profile_info', array('legend'=>__('Profile Information')));



        $fieldset->addField('in_profile_product', 'hidden',
            array(
                'name'  => 'in_profile_product',
                'id'    => 'in_profile_product',
            )
        );


        $fieldset->addField('in_profile_product_old', 'hidden', array('name' => 'in_profile_product_old','id'=>"in_profile_product_old"));

        /*if ($profile->getId()) {
            $form->getElement('profile_code')->setDisabled(1);
        }*/
        //$form->setValues($data->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
