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



namespace Mirasvit\Feed\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Mirasvit\Feed\Api\Service\ExportServiceInterface;
use Mirasvit\Feed\Api\Factory\EntityFactoryInterface;
use Mirasvit\Feed\Helper\Data as DataHelper;

class ExportAction extends Action
{
    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var EntityFactoryInterface
     */
    private $entityFactory;

    /**
     * @var ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @var ExportServiceInterface
     */
    private $exportService;

    public function __construct(
        DataHelper $dataHelper,
        EntityFactoryInterface $entityFactory,
        Context $context,
        ForwardFactory $resultForwardFactory,
        ExportServiceInterface $exportService
    ) {
        $this->dataHelper = $dataHelper;
        $this->entityFactory = $entityFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->exportService = $exportService;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $exportData = $this->getRequest()->getParams();
        $entityName = $this->getRequest()->getParam('export_data');
        $entityMessageName = ucfirst(str_replace('_', ' ', $entityName));

        if (isset($exportData[$entityName])) {
            foreach ($exportData[$entityName] as $entityId) {
                $model = $this->entityFactory->getEntityModelFactory($entityName)->load($entityId);
                $path = $this->dataHelper->getEntityConfigPath($model, $entityName);

                try {
                    $this->exportService->export($model, $path);

                    $this->messageManager->addSuccessMessage(
                        __('%1 has been exported to %2', $entityMessageName, $path)
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($path . ' ' . $e->getMessage());
                }
            }

            return $this->resultRedirectFactory->create()->setPath('*/*/');
        } else {
            $this->messageManager->addErrorMessage(__('%1 has not been selected', $entityMessageName));

            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
    }
}