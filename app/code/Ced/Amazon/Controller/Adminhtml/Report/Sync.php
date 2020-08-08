<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;

/**
 * Class Sync
 * @package Ced\Amazon\Controller\Adminhtml\Report
 */
class Sync extends Action
{
    /**
     * @var \Ced\Amazon\Api\ReportRepositoryInterface
     */
    public $report;

    public $collection;

    public $filter;

    public function __construct(
        Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ced\Amazon\Api\ReportRepositoryInterface $reportRepository,
        \Ced\Amazon\Model\ResourceModel\Report\CollectionFactory $collection
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->report = $reportRepository;
        $this->collection = $collection;
    }

    public function execute()
    {
        $isFilter = $this->getRequest()->getParam('filters');
        if (isset($isFilter)) {
            $collection = $this->filter->getCollection($this->collection->create());
        } else {
            $id = $this->getRequest()->getParam('id');
            if (isset($id) and !empty($id)) {
                $collection = $this->collection->create()->addFieldToFilter('id', ['eq' => $id]);
            }
        }

        foreach ($collection->getItems() as $report) {
            $status = $this->report->sync($report->getId(), $report);
        }

        if ($status != false) {
            $this->messageManager->addSuccessMessage('Report Synced Successfully');
        } else {
            $this->messageManager->addErrorMessage('Report Sync Failed');
        }

        return $this->_redirect('*/report');
    }
}