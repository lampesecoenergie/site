<?php

namespace BoostMyShop\AdminLogger\Block;


class Main extends \Magento\Backend\Block\Widget\Grid\Container
{

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->addData(
            [
                \Magento\Backend\Block\Widget\Container::PARAM_CONTROLLER => 'Log',
                \Magento\Backend\Block\Widget\Grid\Container::PARAM_BLOCK_GROUP => 'BoostMyShop_AdminLogger',
                \Magento\Backend\Block\Widget\Grid\Container::PARAM_BUTTON_NEW => __('Create new Purchase Order'),
                \Magento\Backend\Block\Widget\Container::PARAM_HEADER_TEXT => __('Logs'),
            ]
        );

        $this->addButton(
            'Prune',
            [
                'label' => __('Prune'),
                'onclick' => 'setLocation(\'' . $this->getPruneUrl() . '\')',
                'class' => 'add delete'
            ]
        );

        $this->addButton(
            'Flush',
            [
                'label' => __('Flush'),
                'onclick' => 'setLocation(\'' . $this->getFlushUrl() . '\')',
                'class' => 'add delete'
            ]
        );


        parent::_construct();
    }

    public function getFlushUrl()
    {
        return $this->getUrl('*/*/flush');
    }

    public function getPruneUrl()
    {
        return $this->getUrl('*/*/prune');
    }
}
