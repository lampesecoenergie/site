<?php

namespace BoostMyShop\AdminLogger\Controller\Adminhtml\Main;

use Magento\Framework\Controller\ResultFactory;


class Flush extends \BoostMyShop\AdminLogger\Controller\Adminhtml\Main
{

    /**
     * @return void
     */
    public function execute()
    {

        $resourceModel = $this->_objectManager->get('\BoostMyShop\AdminLogger\Model\ResourceModel\Log');
        $resourceModel->Flush();

        $this->messageManager->addSuccess(__('Logs history flushed'));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
    }
}
