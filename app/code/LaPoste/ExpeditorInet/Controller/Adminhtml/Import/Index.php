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
namespace LaPoste\ExpeditorInet\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * Shimpment form controller.
 *
 * @author Smile (http://www.smile.fr)
 */
class Index extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'LaPoste_ExpeditorInet::import_shipments';

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('LaPoste_ExpeditorInet::import_shipments');
        $resultPage->addBreadcrumb(__('La Poste Expeditor INet'), __('La Poste Expeditor INet'));
        $resultPage->addBreadcrumb(__('Import Shipments'), __('Export Shipments'));
        $resultPage->getConfig()->getTitle()->prepend(__('Import Shipments'));

        return $resultPage;
    }
}
