<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 5/9/19
 * Time: 4:38 PM
 */

namespace Ced\Amazon\Controller\Adminhtml\Processor;

use Ced\Amazon\Api\Processor\BulkActionProcessorInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Process extends \Magento\Framework\App\Action\Action
{
    /** @var \Magento\Framework\Controller\Result\JsonFactory JsonFactory  */
    public $resultJsonFactory;

    /** @var \Magento\Backend\Model\Session  */
    public $session;

    /** @var array  */
    public $actions = [];

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        $actions = []
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->session =  $context->getSession();
        $this->actions =  $actions;
        parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $batchId = $this->getRequest()->getParam('batchid');
        $actionType = $this->getRequest()->getParam('action_type');

        if (isset($batchId)) {
            $ids = [];
            $chunkIds = $this->session->getChunkIds();
            if (isset($chunkIds[$batchId])) {
                $ids = $chunkIds[$batchId];
            }
            try {
                $response = [
                    "success" => false,
                    "message" => $this->getFailureMessage($actionType)
                ];
                /** @var BulkActionProcessorInterface $processor */
                $processor = $this->getProcessor($actionType);
                if (isset($processor)) {
                    $processResponse = $processor->process($ids);
                    if ($processResponse == true) {
                        $response = [
                            "success" => $processResponse,
                            "message" => $this->getSuccessMessage($actionType)
                        ];
                    }
                }
                return $resultJson->setData($response);
            } catch (\Exception $e) {
                $response = [
                    "success" => false,
                    "message" => $e->getMessage()
                ];
                return $resultJson->setData($response);
            }
        }
        return $resultJson->setData([
            "success" => false,
            "message" => 'Batch ' . $batchId . ' not found.'
        ]);
    }

    private function getSuccessMessage($type)
    {
        $message = "";
        if (isset($this->actions[$type]['messages']['success'])) {
            $message = $this->actions[$type]['messages']['success'];
        }
        return $message;
    }

    private function getFailureMessage($type)
    {
        $message = "";
        if (isset($this->actions[$type]['messages']['failure'])) {
            $message = $this->actions[$type]['messages']['failure'];
        }
        return $message;
    }

    private function getProcessor($type)
    {
        $processor = null;
        if (isset($this->actions[$type]['processor'])) {
            $processor = $this->actions[$type]['processor'];
        }
        return $processor;
    }
}
