<?php

namespace Potato\ImageOptimization\Controller\Adminhtml\Filter;

use Potato\ImageOptimization\Controller\Adminhtml\Filter;

class Error extends Filter
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $currentBookmark = $this->getCurrentBookmark();
        $params = $this->getRequest()->getParams();
        $currentConfig = $currentBookmark->getConfig();
        $currentConfig['current']['columns']['error_type'] = ['visible' => true, 'sortable' => true];
        $currentConfig['current']['filters']['applied'] = $params;
        $currentBookmark->setConfig($this->jsonEncode->encode($currentConfig));
        $this->bookmarkRepository->save($currentBookmark);
        return $this->resultRedirectFactory->create()->setPath('po_image/image/index');
    }
}
