<?php

namespace Potato\Crawler\Block\Adminhtml\System\Config;

/**
 * Class Notice
 */
class Notice extends \Magento\Backend\Block\AbstractBlock implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * Render element html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return sprintf(
            '<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><div class="message message-notice notice"><p>%s</p></div></td></tr>',
            $element->getHtmlId(),
            str_replace('\n', '</br>', $element->getLabel())
        );
    }
}