<?php

namespace BoostMyShop\AdminLogger\Controller\Adminhtml\Main;

use Magento\Framework\Controller\ResultFactory;


class Prune extends \BoostMyShop\AdminLogger\Controller\Adminhtml\Main
{

    /**
     * @return void
     */
    public function execute()
    {

        $resourceModel = $this->_objectManager->get('\BoostMyShop\AdminLogger\Model\ResourceModel\Log');
        $resourceModel->prune($this->_config->create()->getPruneDays());

        $this->messageManager->addSuccess(__('Logs history pruned'));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
    }
}
