<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Acyba\GLS\Block\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;


/**
 * Backend system config config field help link
 */
class Help extends \Magento\Config\Block\System\Config\Form\Field
{


    /**
     * Help constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $element->getElementHtml() . '<br/><a href="http://htmlpreview.github.io/?https://github.com/owebia/magento2-module-advanced-shipping-setting/blob/master/view/doc_en_US.html" target="_blank">' . __('Help') . '</a>';
    }
}

