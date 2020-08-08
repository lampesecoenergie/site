<?php

namespace Potato\Crawler\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Potato\Crawler\Api\QueueRepositoryInterface;
use Potato\Crawler\Api\CounterRepositoryInterface;
use Potato\Crawler\Model\Warmer;

/**
 * Class Priority
 */
class Priority extends Field
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
        $this->setTemplate('Potato_Crawler::system/config/priority.phtml');
    }
    
    /**
     * @param  AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->setElement($element);
        return $this->_toHtml();
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->sortOptions($this->getElement()->getValues());
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->getElement()->getName();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->getElement()->getId();
    }

    /**
     * Sort options by priority
     *
     * @param $options
     * @return array
     */
    private function sortOptions($options)
    {
        if (!$values = $this->getElement()->getValue()) {
            return $options;
        }
        $valuesArray = explode(',', $values);
        $result = [];
        foreach ($valuesArray as $value) {
            foreach ($options as $option) {
                if ($option['value'] == $value) {
                    $result[] = $option;
                    break;
                }
            }
        }
        foreach ($options as $option) {
            if (!in_array($option['value'], $valuesArray)) {
                $result[] = $option;
            }
        }
        return $result;
    }

    /**
     * Check if option disabled
     *
     * @param $value
     * @return bool
     */
    public function isDisabled($value)
    {
        if ($values = $this->getElement()->getValue()) {
            $valuesArray = explode(",", $values);
            if (!in_array($value, $valuesArray)) {
                return true;
            }
        }
        return false;
    }
}