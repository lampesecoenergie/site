<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 11/9/19
 * Time: 9:44 AM
 */

namespace Ced\Amazon\Block\Adminhtml\Processor;

class Create extends \Magento\Backend\Block\Widget\Container
{
    /** @var \Magento\Framework\Session\SessionManagerInterface  */
    public $session;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\App\Action\Context $actionContext,
        array $data = []
    ) {
        $this->session =  $actionContext->getSession();
        parent::__construct($context, $data);
        $this->_getAddButtonOptions();
    }

    /**
     * Add Back button
     */
    public function _getAddButtonOptions()
    {
        $splitButtonOptions = [
            'label' => __('Back'),
            'class' => 'action-secondary',
            'onclick' => "setLocation('" . $this->getCreateUrl() . "')"
        ];
        $this->buttonList->add('add', $splitButtonOptions);
    }

    public function getCreateUrl()
    {
        $backUrl = $this->session->getBackUrl();
        if ($backUrl) {
            return $this->getUrl($backUrl);
        }
        return $this->getUrl('amazon/order/index');
    }

    public function getIds()
    {
        $ids = $this->session->getChunkIds();
        return $ids;
    }

    public function getChunksCount()
    {
          $chunkIds = $this->session->getChunkIds();
          return count($chunkIds);
    }

    public function getActionType()
    {
        $actionType = $this->session->getActionType();
        return $actionType;
    }

    public function getAjaxUrl()
    {
        return $this->getUrl('amazon/processor/process');
    }

    public function finalMessage()
    {
        $actionMessage = $this->session->getActionMessage();
        if (isset($actionMessage['finished_msg'])) {
            return $actionMessage['finished_msg'];
        } else {
            return 'Finished';
        }
    }
}