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


namespace Mirasvit\Feed\Block\Adminhtml\Template\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Template Information'));
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeToHtml()
    {
        $this->addTab('general_section', [
            'label'   => __('Template Information'),
            'title'   => __('Template Information'),
            'content' => $this->getLayout()->createBlock('\Mirasvit\Feed\Block\Adminhtml\Template\Edit\Tab\General')
                ->toHtml(),
        ]);

        $this->addTab('csv_section', [
            'label'   => __('Content Settings'),
            'title'   => __('Content Settings'),
            'content' => $this->getLayout()->createBlock('\Mirasvit\Feed\Block\Adminhtml\Template\Edit\Tab\Schema\Csv')
                ->toHtml(),
        ]);

        $this->addTab('xml_section', [
            'label'   => __('Content Settings'),
            'title'   => __('Content Settings'),
            'content' => $this->getLayout()->createBlock('\Mirasvit\Feed\Block\Adminhtml\Template\Edit\Tab\Schema\Xml')
                ->toHtml(),
        ]);

        return parent::_beforeToHtml();
    }
}
