<?php

namespace Potato\ImageOptimization\Controller\Adminhtml\Image;

use Potato\ImageOptimization\Controller\Adminhtml\Image;
use Potato\ImageOptimization\Manager\Restore as RestoreManager;
use Magento\Backend\App\Action;
use Potato\ImageOptimization\Model\ResourceModel\ImageRepository;

class Restore extends Image
{
    /** @var RestoreManager  */
    protected $restoreManager;

    /**
     * @param Action\Context $context
     * @param ImageRepository $imageRepository
     * @param RestoreManager $restoreManager
     */
    public function __construct(
        Action\Context $context,
        ImageRepository $imageRepository,
        RestoreManager $restoreManager
    ) {
        parent::__construct($context, $imageRepository);
        $this->restoreManager = $restoreManager;
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
            $this->restoreManager->restoreImage($image);
            $this->messageManager->addSuccessMessage(__('The image has been successfully restored.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('*/*/');
    }
}
