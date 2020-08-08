<?php

namespace Potato\ImageOptimization\Controller\Adminhtml\Image;

use Potato\ImageOptimization\Controller\Adminhtml\Image;
use Magento\Ui\Component\MassAction\Filter;
use Potato\ImageOptimization\Model\ResourceModel\Image\CollectionFactory as GridCollectionFactory;
use Potato\ImageOptimization\Model\ResourceModel\Image\Collection as GridCollection;
use Magento\Backend\App\Action;
use Potato\ImageOptimization\Model\ResourceModel\ImageRepository;
use Potato\ImageOptimization\Manager\Optimization as OptimizationManager;

class MassOptimize extends Image
{
    /** @var GridCollection  */
    protected $collection;

    /** @var Filter  */
    protected $filter;

    /** @var OptimizationManager  */
    protected $optimizationManager;

    /**
     * @param Action\Context $context
     * @param ImageRepository $imageRepository
     * @param OptimizationManager $optimizationManager
     * @param Filter $filter
     * @param GridCollectionFactory $collection
     */
    public function __construct(
        Action\Context $context,
        ImageRepository $imageRepository,
        OptimizationManager $optimizationManager,
        Filter $filter,
        GridCollectionFactory $collection
    ) {
        parent::__construct($context, $imageRepository);
        $this->collection = $collection->create();
        $this->filter = $filter;
        $this->optimizationManager = $optimizationManager;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var GridCollection $collection */
        $collection = $this->filter->getCollection($this->collection);
        $count = 0;
        foreach ($collection->getItems() as $imageItem) {
            try {
                $image = $this->imageRepository->get($imageItem->getId());
                $this->optimizationManager->optimizeImage($image);
                $count++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been updated.', $count)
        );
        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
