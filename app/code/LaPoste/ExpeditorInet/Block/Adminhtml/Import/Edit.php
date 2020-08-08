<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module
 * to newer versions in the future.
 *
 * @copyright 2017 La Poste
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace LaPoste\ExpeditorInet\Block\Adminhtml\Import;

use Magento\Backend\Block\Widget\Form\Container;

/**
 * Import form container.
 *
 * @author Smile (http://www.smile.fr)
 */
class Edit extends Container
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        parent::_construct();

        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
        $this->buttonList->update('save', 'label', __('Import'));
        $this->buttonList->update('save', 'id', 'import_button');

        $this->_blockGroup = 'LaPoste_ExpeditorInet';
        $this->_controller = 'adminhtml_import';
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderText()
    {
        return __('Import Shipments');
    }
}
