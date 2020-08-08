<?php

namespace Potato\ImageOptimization\Controller\Adminhtml\Image;

use Potato\ImageOptimization\Controller\Adminhtml\Image;
use Magento\Ui\Component\MassAction\Filter;
use Potato\ImageOptimization\Model\ResourceModel\Image\CollectionFactory as GridCollectionFactory;
use Potato\ImageOptimization\Model\ResourceModel\Image\Collection as GridCollection;
use Magento\Backend\App\Action;
use Potato\ImageOptimization\Model\ResourceModel\ImageRepository;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;

class MassStatus extends Image
{
    /** @var GridCollection  */
    protected $collection;

    /** @var Filter  */
    protected $filter;

    /**
     * @param Action\Context $context
     * @param ImageRepository $imageRepository
     * @param Filter $filter
     * @param GridCollectionFactory $collection
     */
    public function __construct(
        Action\Context $context,
        ImageRepository $imageRepository,
        Filter $filter,
        GridCollectionFactory $collection
    ) {
        parent::__construct($context, $imageRepository);
        $this->collection = $collection->create();
        $this->filter = $filter;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $status = $this->getRequest()->getParam('status', null);
        if (null === $status) {
            $this->messageManager->addErrorMessage(__('Status is not found.'));
            return $resultRedirect->setPath('*/*/');
        }

        $this->collection = $this->filter->getCollection($this->collection);
        $count = 0;
        foreach ($this->collection->getItems() as $imageItem) {
            try {
                $image = $this->imageRepository->get($imageItem->getId());
                if (!file_exists($image->getPath())) {
                    $image->delete();
                    $count++;
                    continue;
                }
                $image
                    ->setStatus($status)
                    ->setResult(__("Status has been changed"));

                if ($image->getStatus() === StatusSource::STATUS_OPTIMIZED) {
                    $image->setTime(filemtime($image->getPath()));
                }
                $image->save();
                $count++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been updated.', $count)
        );
        return $resultRedirect->setRefererUrl();
    }
}
