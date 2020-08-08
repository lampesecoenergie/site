<?php

namespace Potato\ImageOptimization\Model\ResourceModel;

use Potato\ImageOptimization\Model as ImageModel;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Magento\Framework\DB\Select;

class ImageRepository
{

    const PROCESS_OPTIMIZATION_IMAGE_LIMIT = 50;
    const PROCESS_SCAN_DATABASE_LIMIT = 500;

    /** @var ImageModel\ImageFactory  */
    protected $imageFactory;

    /** @var Image  */
    protected $imageResource;

    /**
     * @param ImageModel\ImageFactory $imageFactory
     * @param Image $imageResource
     */
    public function __construct(
        ImageModel\ImageFactory $imageFactory,
        Image $imageResource
    ) {
        $this->imageFactory = $imageFactory;
        $this->imageResource = $imageResource;
    }

    /**
     * Create new empty image model
     * @return ImageModel\Image
     */
    public function create()
    {
        return $this->imageFactory->create();
    }
    

    /**
     * @param int $imageId
     * @return ImageModel\Image
     */
    public function get($imageId)
    {
        /** @var Image\Collection $collection */
        $collection = $this->getImageCollection();
        $collection->addFieldToFilter('id', ['eq' => $imageId]);
        /** @var ImageModel\Image $item */
        $item = $collection->getFirstItem();
        return $item;
    }

    /**
     * @param array $statusList
     * @return Image\Collection
     */
    public function getCollectionByStatusList($statusList)
    {
        /** @var Image\Collection $collection */
        $collection = $this->getImageCollection();
        $collection->addFieldToFilter('status', ['in' => $statusList]);
        return $collection;
    }

    /**
     * @param string $path
     * @return \Magento\Framework\DataObject
     */
    public function getByPath($path)
    {
        /** @var Image\Collection $collection */
        $collection = $this->getImageCollection();
        $collection->addFieldToFilter('path', ['eq' => $path]);
        return $collection->getFirstItem();
    }

    /**
     * @return Image\Collection
     */
    public function getImageCollection()
    {
        $collection = $this->imageFactory->create()->getCollection();
        return $collection;
    }

    /**
     * @return Image\Collection
     */
    public function getCollectionErrorTypeGroup()
    {
        $collection = $this->getImageCollection();
        $collection->getSelect()->reset(Select::COLUMNS);
        $collection->getSelect()->columns([
            'code' => 'main_table.error_type',
            'count' => 'COALESCE(COUNT(main_table.id), 0)'
        ]);
        $collection->addFieldToFilter('status', ['eq' => StatusSource::STATUS_ERROR]);
        $collection->getSelect()->group('main_table.error_type');
        return $collection;
    }

    /**
     * @param null|int $limit
     * @return Image\Collection
     */
    public function getCollectionForOptimization($limit = null)
    {
        $collection = $this->getCollectionByStatusList([StatusSource::STATUS_PENDING, StatusSource::STATUS_OUTDATED]);
        $pageSize = self::PROCESS_OPTIMIZATION_IMAGE_LIMIT;
        if ($limit && $limit < $pageSize) {
            $pageSize = $limit;
        }
        $collection->setPageSize($pageSize);
        return $collection;
    }

    /**
     * @param int $page
     * @return Image\Collection
     */
    public function getCollectionForScanDbPerPage($page)
    {
        $collection = $this->getImageCollection();
        $collection
            ->setPageSize(self::PROCESS_SCAN_DATABASE_LIMIT)
            ->setCurPage($page)
        ;
        return $collection;
    }
}
