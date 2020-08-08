<?php

namespace Ced\Amazon\Block\Adminhtml\Profile\Ui\Button;

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
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/profile/save');
    }
}
