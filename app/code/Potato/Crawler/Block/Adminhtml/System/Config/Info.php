<?php

namespace Potato\Crawler\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Potato\Crawler\Model\Warmer;
use Potato\Crawler\Model\Lock\Warmer as WarmerLock;
use Potato\Crawler\Model\Lock\Queue as QueueLock;

/**
 * Class Queue
 */
class Info extends Field
{
    /** @var Warmer  */
    protected $warmer;

    protected $queueLock;

    protected $warmerLock;

    public function __construct(
        Context $context,
        Warmer $warmer,
        WarmerLock $warmerLock,
        QueueLock $queueLock,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->warmer = $warmer;
        $this->warmerLock = $warmerLock;
        $this->queueLock = $queueLock;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        $this->_setElementValue($element);
        return parent::render($element);
    }

    protected function _setElementValue($element)
    {
        $element->setAfterElementHtml('<style>#po_crawler_info .control-value{padding-top:7px;}</style>');
        if(!function_exists('exec')) {
            $element->setValue(__('Please, enable "exec" function.'));
            return $this;
        }

        if ($element->getData('field_config/id') == 'nproc') {
            $element->setValue((string)$this->warmer->getCpuCoresNumber());
            return $this;
        }
        if ($element->getData('field_config/id') == 'thread') {
            $element->setValue((string)$this->warmer->getThreadCount());
            return $this;
        }
        if ($element->getData('field_config/id') == 'warmer_activity') {
            $element->setValue($this->warmerLock->getLastActivity() . ' UTC');
            return $this;
        }
        if ($element->getData('field_config/id') == 'warmer_pid') {
            $pid = $this->warmerLock->getProcessPid();
            if (!$pid) {
                $pid = __(' - ');
            }
            if ($this->warmerLock->isPidRunning()) {
                $pid .= __("(Running)");
            } else {
                $pid .= __("(Complete)");
            }
            $element->setValue($pid);
            return $this;
        }
        if ($element->getData('field_config/id') == 'queue_activity') {
            $element->setValue($this->queueLock->getLastActivity() . ' UTC');
            return $this;
        }
        if ($element->getData('field_config/id') == 'queue_pid') {
            $pid = $this->queueLock->getProcessPid();
            if (!$pid) {
                $pid = __(' - ');
            }
            if ($this->queueLock->isPidRunning()) {
                $pid .= __("(Running)");
            } else {
                $pid .= __("(Complete)");
            }
            $element->setValue((string)$pid);
            return $this;
        }
        if ($element->getData('field_config/id') == 'critical') {
            $element->setValue((string)$this->warmer->getFullLoadValue());
            return $this;
        }
        if ($element->getData('field_config/id') == 'current') {
            $element->setValue((string)$this->warmer->getCurrentCpuLoadAvg());
            return $this;
        }
        if ($element->getData('field_config/id') == 'acceptable') {
            $element->setValue((string)$this->warmer->getAcceptableLoadAverage());
            return $this;
        }
    }
}