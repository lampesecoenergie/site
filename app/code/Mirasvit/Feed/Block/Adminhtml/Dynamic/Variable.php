<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Block\Adminhtml\Dynamic;

use Magento\Backend\Block\Widget\Grid\Container;

class Variable extends Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_dynamic_variable';
        $this->_blockGroup = 'Mirasvit_Feed';
        $this->_headerText = __('Manage Dynamic Variables');
        $this->_addButtonLabel = __('Add Variable');

        parent::_construct();
    }
}
