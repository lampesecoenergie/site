<?php

namespace Potato\Crawler\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Restart
 */
class Restart extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @param  AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $url = $this->getUrl('po_crawler/process/restart', array('type' => $element->getData('field_config/id')));
        $html = '<a class="action-default" href="' . $url .'">' . $element->getLabel() . '</a>';
        return $html;
    }
}