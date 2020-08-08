<?php

namespace Ced\Amazon\Block\Adminhtml\Account\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Delete implements ButtonProviderInterface
{

    /**
     * @var \Magento\Backend\Block\Widget\Container
     */
    public $container;
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    public $urlBuilder;
    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * Delete constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Widget\Container $container
     */

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Container $container
    ) {
        $this->container = $container;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->registry = $registry;
    }
    /**
     * @return array|bool
     */
    public function getButtonData()
    {
        $id = $this->container->getRequest()->getParam('id');
        if (!empty($id)) {
            $data = [
                'class' => 'action-secondary scalable delete',
                'label' => __('Delete'),
                'on_click' => "deleteConfirm('Are you sure you want to do this account?','".$this->getDeleteUrl($id)."')",
                'sort_order' => 20,
                'data_attribute' => [
                    'url' => $this->getDeleteUrl($id)
                ],
            ];
            return $data;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getDeleteUrl($id)
    {
        return $this->urlBuilder->getUrl('*/account/delete', ['id' => $id]);
    }
}
