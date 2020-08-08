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



namespace Mirasvit\Feed\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container as GridContainer;

class Template extends GridContainer
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_template';
        $this->_blockGroup = 'Mirasvit_Feed';
        $this->_headerText = __('Manage Feed Templates');
        $this->_addButtonLabel = __('Add Template');

        $this->buttonList->add('import', [
            'label'   => __('Import Templates'),
            'onclick' => 'setLocation(\'' . $this->getUrl('*/*/import') . '\')',
            'class'   => 'import',
        ]);

        parent::_construct();
    }
}
