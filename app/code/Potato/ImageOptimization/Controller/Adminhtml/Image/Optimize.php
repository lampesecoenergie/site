<?php

namespace Potato\ImageOptimization\Controller\Adminhtml\Image;

use Potato\ImageOptimization\Controller\Adminhtml\Image;
use Magento\Backend\App\Action;
use Potato\ImageOptimization\Model\ResourceModel\ImageRepository;
use Potato\ImageOptimization\Manager\Optimization as OptimizationManager;

class Optimize extends Image
{
    /** @var OptimizationManager  */
    protected $optimizationManager;

    /**
     * @param Action\Context $context
     * @param ImageRepository $imageRepository
     * @param OptimizationManager $optimizationManager
     */
    public function __construct(
        Action\Context $context,
        ImageRepository $imageRepository,
        OptimizationManager $optimizationManager
    ) {
        parent::__construct($context, $imageRepository);
        $this->optimizationManager = $optimizationManager;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        if (!$id) {
            $this->messageManager->addErrorMessage(__('The image no longer exists.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $image = $this->imageRepository->get($id);
            $this->optimizationManager->optimizeImage($image);
            $this->messageManager->addSuccessMessage(__('The image has been optimized. Check result'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('*/*/');
    }
}
