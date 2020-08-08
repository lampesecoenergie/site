<?php
/**
 * System configuration schema locator
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ced\RueDuCommerce\Model\Config;

use Magento\Framework\Module\Dir;

class SchemaLocator extends \Magento\Config\Model\Config\SchemaLocator
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    protected $_schema = null;

    /**
     * Path to corresponding XSD file with validation rules for separate config files
     *
     * @var string
     */
    protected $_perFileSchema = null;
    protected $_objectManager;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleReader)
    {

        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if ($this->_objectManager->get('Magento\Framework\Module\Manager')->isEnabled('Ced_RueDuCommerce')) {
            $etcDir = $moduleReader->getModuleDir(\Magento\Framework\Module\Dir::MODULE_ETC_DIR, 'Ced_RueDuCommerce');
            $this->_schema = $etcDir . '/system.xsd';
            $this->_perFileSchema = $etcDir . '/system_file.xsd';
        } else {
            return parent::__construct($moduleReader);
        }
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * Get path to pre file validation schema
     *
     * @return string|null
     */
    public function getPerFileSchema()
    {
        return $this->_perFileSchema;
    }
}
