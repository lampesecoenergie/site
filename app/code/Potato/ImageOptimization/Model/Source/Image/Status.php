<?php

namespace Potato\ImageOptimization\Model\Source\Image;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    const STATUS_PENDING   = 'pending';
    const STATUS_ERROR     = 'error';
    const STATUS_OPTIMIZED = 'optimized';
    const STATUS_OUTDATED  = 'outdated';
    const STATUS_SKIPPED   = 'skipped';
    const STATUS_IN_PROGRESS = 'in_progress';
    
    /**
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::STATUS_PENDING => __("Pending"),
            self::STATUS_ERROR => __("Error"),
            self::STATUS_OPTIMIZED => __("Optimized"),
            self::STATUS_OUTDATED => __("Outdated"),
            self::STATUS_SKIPPED => __("Skipped"),
            self::STATUS_IN_PROGRESS => __("In Progress"),
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->getOptionArray();
        $result = [];
        foreach ($options as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }
        return $result;
    }
}
