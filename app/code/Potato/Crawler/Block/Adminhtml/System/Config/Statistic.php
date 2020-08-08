<?php

namespace Potato\Crawler\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Potato\Crawler\Api\QueueRepositoryInterface;
use Potato\Crawler\Api\CounterRepositoryInterface;
use Potato\Crawler\Model\Warmer;

/**
 * Class Statistic
 */
class Statistic extends Field
{
    /** @var QueueRepositoryInterface  */
    protected $queueRepository;

    /** @var CounterRepositoryInterface  */
    protected $counterRepository;

    /** @var Warmer  */
    protected $warmer;

    protected $cpuLoad = null;
    protected $quote = null;
    protected $counter = null;

    public function __construct(
        Context $context,
        QueueRepositoryInterface $queueRepository,
        CounterRepositoryInterface $counterRepository,
        Warmer $warmer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->queueRepository = $queueRepository;
        $this->counterRepository = $counterRepository;
        $this->warmer = $warmer;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Potato_Crawler::system/config/statistic.phtml');
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

    public function canShow()
    {
        return $this->warmer->isWin() !== true;
    }

    /**
     * @return float|int
     */
    public function getCpuLoadAngle()
    {
        return $this->getPieAngle($this->getCpuLoad());
    }

    /**
     * @return float|null
     */
    public function getCpuLoad()
    {
        if (null === $this->cpuLoad) {
            $this->cpuLoad = round($this->warmer->getCurrentCpuLoad());
        }
        return $this->cpuLoad;
    }

    /**
     * @return float|int
     */
    public function getWarmerAngle()
    {
        if (!$progress = $this->getWarmerProgress()) {
            return 2;
        }
        return $this->getPieAngle($progress);
    }

    /**
     * @return float|int
     */
    public function getWarmerProgress()
    {
        if (!$this->getCounterSize() && !$this->getQuoteSize()) {
            return 100;
        }
        if (!$this->getCounterSize()) {
            return 0;
        }
        return round($this->getCounterSize() * 100 / ($this->getQuoteSize() + $this->getCounterSize()));
    }

    /**
     * @return string
     */
    public function getWarmerStat()
    {
        $total = $this->getQuoteSize() + $this->getCounterSize();
        return $this->getCounterSize() . " / " . $total;
    }

    /**
     * @return null
     */
    protected function getQuoteSize()
    {
        if (null === $this->quote) {
            $this->quote = $this->queueRepository->getQueueSize();
        }
        return $this->quote;
    }

    /**
     * @return int|null
     */
    protected function getCounterSize()
    {
        if (null === $this->counter) {
            $counterStatistic = $this->counterRepository->getListForToday()->getItems();
            $counter = array_shift($counterStatistic);
            $this->counter = null !== $counter ? (int)$counter->getValue() : 0;
        }
        return $this->counter;
    }

    /**
     * Get Pi value for pie angle
     *
     * @param int $value
     * @return float|int
     */
    protected function getPieAngle($value)
    {
        if (!$value) {
            return 0;
        }
        return 2 * $value / 100 + 2;
    }

    /**
     * Get current warmer speed
     *
     * @return int
     */
    public function getCurrentSpeed()
    {
        return $this->warmer->getCurrentSpeed();
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
}