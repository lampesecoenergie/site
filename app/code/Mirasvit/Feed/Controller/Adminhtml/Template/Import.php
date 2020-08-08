<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Feed\Controller\Adminhtml\Template;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Feed\Controller\Adminhtml\Template;

class Import extends Template
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($templates = $this->getRequest()->getParam('template')) {
            foreach ($templates as $templatePath) {
                try {
                    $model = $this->templateFactory->create()->import($templatePath);
                    $this->messageManager->addSuccess(__('Template "%1" has been imported.', $model->getName()));
                } catch (\Exception $e) {
                    $this->messageManager->addError($templatePath . ' ' . $e->getMessage());
                }
            }

            return $this->resultRedirectFactory->create()->setPath('*/*/');
        } else {
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

            $this->initPage($resultPage)
                ->getConfig()->getTitle()->prepend(__('Import Feed Templates'));

            return $resultPage;
        }
    }
}
