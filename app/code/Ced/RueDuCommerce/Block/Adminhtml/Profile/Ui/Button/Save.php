<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 15/1/18
 * Time: 1:39 PM
 */

namespace Ced\RueDuCommerce\Block\Adminhtml\Profile\Ui\Button;


use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Save extends GenericButton implements ButtonProviderInterface
{

    /**
     * Retrieve button-specified settings
     *
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
            'on_click' => $this->getBackUrl()
        ];
    }

    public function getBackUrl()
    {
        return $this->getUrl('rueducommerce/profile/save');
    }
}