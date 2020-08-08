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

use Mirasvit\Feed\Controller\Adminhtml\Template;

class Export extends Template
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $model = $this->initModel();
        $path = $model->export();

        $this->messageManager->addSuccess(__('Template "%1" has been exported to "%2"', $model->getName(), $path));

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
