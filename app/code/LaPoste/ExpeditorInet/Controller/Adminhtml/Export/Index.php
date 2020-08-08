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
namespace LaPoste\ExpeditorInet\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * Order grid controller.
 *
 * @author Smile (http://www.smile.fr)
 */
class Index extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'LaPoste_ExpeditorInet::export_orders';

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('LaPoste_ExpeditorInet::export_orders');
        $resultPage->addBreadcrumb(__('La Poste Expeditor INet'), __('La Poste Expeditor INet'));
        $resultPage->addBreadcrumb(__('Export to Expeditor INet'), __('Export to Expeditor INet'));
        $resultPage->getConfig()->getTitle()->prepend(__('Export to Expeditor INet'));

        return $resultPage;
    }
}
