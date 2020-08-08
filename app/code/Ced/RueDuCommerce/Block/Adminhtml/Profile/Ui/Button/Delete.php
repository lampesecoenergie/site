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

class Delete extends GenericButton implements ButtonProviderInterface
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
        if ($id = $this->container->getRequest()->getParam('id')) {
            $data = [
                'class' => 'action-secondary scalable delete',
                'label' => __('Delete'),
                'on_click' => "deleteConfirm('Are you sure you want to do this image?','".$this->getDeleteUrl($id)."')",
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
        return $this->getUrl('*/profile/delete', ['id' => $id]);
    }
}