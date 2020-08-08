<?php
namespace Potato\ImageOptimization\Model\ResourceModel\Image;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Potato\ImageOptimization\Model;
use Magento\Framework\DB\Select;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(
            Model\Image::class,
            Model\ResourceModel\Image::class
        );
    }

    /**
     * @param string $valueField
     * @param string $labelField
     * @return array
     */
    protected function _toOptionHash($valueField = 'id', $labelField = 'path')
    {
        return parent::_toOptionHash($valueField, $labelField);
    }

}
