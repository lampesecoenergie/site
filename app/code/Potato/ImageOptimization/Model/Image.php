<?php
namespace Potato\ImageOptimization\Model;

use Magento\Framework;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Magento\Framework\Model\AbstractModel;

/**
 * @method int getId()
 * @method $this setId(int $id)
 * @method string getPath()
 * @method $this setPath(string $path)
 * @method string getStatus()
 * @method $this setStatus(string $status)
 * @method int getTime()
 * @method $this setTime(int $time)
 * @method string getResult()
 * @method $this setResult(string $result)
 * @method null|string getErrorType()
 * @method $this setErrorType($errorType)
 */
class Image extends AbstractModel
{
    /**
     * @param Framework\Model\Context $context
     * @param Framework\Registry $registry
     * @param ResourceModel\Image $resource
     * @param ResourceModel\Image\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Framework\Model\Context $context,
        Framework\Registry $registry,
        ResourceModel\Image $resource,
        ResourceModel\Image\Collection $resourceCollection,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource mode
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Image::class);
    }

    /**
     * Processing object before save data
     *
     * @return $this
     */
    public function beforeSave()
    {
        if ($this->getStatus() != StatusSource::STATUS_ERROR) {
            $this->setErrorType(null);
        }
        parent::beforeSave();
        return $this;
    }
}
