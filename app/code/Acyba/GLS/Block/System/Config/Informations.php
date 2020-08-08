<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Acyba\GLS\Block\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\ResourceInterface;

/**
 * Backend system config datetime field renderer
 */
class Informations extends \Magento\Config\Block\System\Config\Form\Field
{

    protected $_moduleResource;

    /**
     * Informations constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param ResourceInterface $moduleresource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ResourceInterface $moduleresource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_moduleResource = $moduleresource;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return 'Version: ' . $this->_moduleResource->getDbVersion('Acyba_GLS');
    }
}