<?php

namespace Potato\ImageOptimization\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Potato\ImageOptimization\Model\Source\Image\Status as ImageStatus;
use Potato\ImageOptimization\Model\ResourceModel\ImageRepository;
use Potato\ImageOptimization\Model\ResourceModel\Image\CollectionFactory as ImageCollectionFactory;
use Potato\ImageOptimization\Model\Source\Optimization\Error as ErrorSource;
use Potato\ImageOptimization\Model\Lock;
use Potato\ImageOptimization\Manager\Optimization;

class Status extends Field
{
    /** @var ImageRepository  */
    protected $imageRepository;

    /** @var ImageCollectionFactory  */
    protected $imageCollectionFactory;

    /** @var ErrorSource  */
    protected $errorSource;

    /** @var Lock  */
    protected $lock;

    /** @var Optimization */
    protected $optimizationManager;

    protected $errorGroupTypes = [];

    protected $errorGroupTypesTotal = 0;

    /**
     * @param Context $context
     * @param ImageRepository $imageRepository
     * @param ImageCollectionFactory $imageCollectionFactory
     * @param ErrorSource $errorSource
     * @param Lock $lock
     * @param Optimization $optimizationManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        ImageRepository $imageRepository,
        ImageCollectionFactory $imageCollectionFactory,
        ErrorSource $errorSource,
        Lock $lock,
        Optimization $optimizationManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->errorSource = $errorSource;
        $this->imageRepository = $imageRepository;
        $this->imageCollectionFactory = $imageCollectionFactory;
        $this->lock = $lock;
        $this->optimizationManager = $optimizationManager;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Potato_ImageOptimization::system/config/status.phtml');
    }
    
    /**
     * @param  AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return float
     */
    public function getProgressPercentValue()
    {
        $totalImages = $this->getTotalCount();
        $completedImages = $this->getOptimizedCount();
        if ($totalImages === 0) {
            return 0;
        }
        return round(100 / $totalImages * $completedImages);
    }

    /**
     * @return int
     */
    public function getOptimizedCount()
    {
        $processedImages = $this->imageRepository
            ->getCollectionByStatusList([ImageStatus::STATUS_OPTIMIZED, ImageStatus::STATUS_SKIPPED]);
        return $processedImages->getSize();
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->imageRepository->getImageCollection()->getSize();
    }

    /**
     * @return array
     */
    public function getErrorByGroup()
    {
        if ($this->errorGroupTypes) {
            return $this->errorGroupTypes;
        }
        /** @var \Potato\ImageOptimization\Model\ResourceModel\Image\Collection $imageCollection */
        $imageCollection = $this->imageRepository->getCollectionErrorTypeGroup();
        foreach ($imageCollection->getItems() as $key => $row) {
            $this->errorGroupTypes[$key]['text'] = $this->errorSource->getLabelByCode($row['code']) . ' - ';
            $this->errorGroupTypes[$key]['code'] = $row['code'];
            $this->errorGroupTypes[$key]['count'] = $row['count'];
            $this->errorGroupTypesTotal += $row['count'];
        }
        return $this->errorGroupTypes;
    }

    public function getErrorByGroupTotal()
    {
        $this->getErrorByGroup();
        return $this->errorGroupTypesTotal;
    }

    /**
     * @return string
     */
    public function getSkippedUrl()
    {
        return $this->getUrl('po_image/filter/status', ['status' => ImageStatus::STATUS_SKIPPED]);
    }

    /**
     * @param string $code
     * @return string
     */
    public function getErrorUrlByCode($code)
    {
        return $this->getUrl('po_image/filter/error', ['error_type' => $code]);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        $html = $this->_renderValue($element);
        return $this->_decorateRowHtml($element, $html);
    }

    public function getScanLastTime()
    {
        $time = $this->lock->getLockFileTime(Lock::SCAN_LOCK_FILE);
        if (!$time) {
            return __("Not started yet");
        }
        $date = new \DateTime();
        return $date->setTimestamp($time)->format("H:i, d M Y");
    }

    public function getOptimizationLastTime()
    {
        $time = $this->lock->getLockFileTime(Lock::OPTIMIZATION_LOCK_FILE);
        if (!$time) {
            return __("Not started yet");
        }
        $date = new \DateTime();
        return $date->setTimestamp($time)->format("H:i, d M Y");
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLibStatusLabel()
    {
        $statusList = $this->optimizationManager->getOptimizationLibStatusList();
        $notAvailableLibs = [];
        foreach ($statusList as $lib => $isAvailable) {
            if ($isAvailable) {
                continue;
            }
            $notAvailableLibs[] = $lib;
        }
        if (!$notAvailableLibs) {
            $label = __('All libs available');
        } else {
            $label = __('%1 not available', implode(', ', $notAvailableLibs));
        }

        return $label;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getGridViewButtonHtml()
    {
        /** @var \Magento\Backend\Block\Widget\Button $button */
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'images_list',
                'label' => __('View Processed Images'),
                'onclick' => "setLocation('" . $this->getUrl('po_image/image/index') . "')",
            ]
        );
        return $button->toHtml();
    }


    /**
     * @return float|int
     */
    public function getProgressAngle()
    {
        return  $this->getProgressPercentValue() * 3.6;
    }
}
