<?php

namespace Potato\ImageOptimization\Controller\Adminhtml\Image;

use Potato\ImageOptimization\Controller\Adminhtml\Image;
use Magento\Ui\Component\MassAction\Filter;
use Potato\ImageOptimization\Model\ResourceModel\Image\CollectionFactory as GridCollectionFactory;
use Potato\ImageOptimization\Model\ResourceModel\Image\Collection as GridCollection;
use Magento\Backend\App\Action;
use Potato\ImageOptimization\Model\ResourceModel\ImageRepository;
use Potato\ImageOptimization\Manager\Restore as RestoreManager;

class MassRestore extends Image
{
    /** @var GridCollection  */
    protected $collection;

    /** @var Filter  */
    protected $filter;

    /** @var RestoreManager  */
    protected $restoreManager;

    /**
     * @param Action\Context $context
     * @param ImageRepository $imageRepository
     * @param RestoreManager $restoreManager
     * @param Filter $filter
     * @param GridCollectionFactory $collection
     */
    public function __construct(
        Action\Context $context,
        ImageRepository $imageRepository,
        RestoreManager $restoreManager,
        Filter $filter,
        GridCollectionFactory $collection
    ) {
        parent::__construct($context, $imageRepository);
        $this->collection = $collection->create();
        $this->filter = $filter;
        $this->restoreManager = $restoreManager;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $this->collection = $this->filter->getCollection($this->collection);
        $count = 0;

        foreach ($this->collection->getItems() as $imageItem) {
            try {
                $image = $this->imageRepository->get($imageItem->getId());
                $this->restoreManager->restoreImage($image);
                $count++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been updated.', $count)
        );
        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
