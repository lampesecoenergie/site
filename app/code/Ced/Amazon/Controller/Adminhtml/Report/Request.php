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
 * Class Request
 * @package Ced\Amazon\Controller\Adminhtml\Report
 */
class Request extends Action
{
    /** @var \Ced\Amazon\Api\AccountRepositoryInterface */
    public $account;

    /** @var \Ced\Amazon\Api\Data\Queue\DataInterfaceFactory */
    public $queueDataFactory;

    /**
     * @var \Ced\Amazon\Api\QueueRepositoryInterface
     */
    public $queue;

    public function __construct(
        Action\Context $context,
        \Ced\Amazon\Api\AccountRepositoryInterface $account,
        \Ced\Amazon\Api\QueueRepositoryInterface $queueRepository,
        \Ced\Amazon\Api\Data\Queue\DataInterfaceFactory $queueDataFactory
    ) {
        parent::__construct($context);
        $this->account = $account;
        $this->queue = $queueRepository;
        $this->queueDataFactory = $queueDataFactory;
    }

    public function execute()
    {
        $accountId = $this->getRequest()->getParam("account_id");
        $startDate = $this->getRequest()->getParam("start_date", null);
        $endDate = $this->getRequest()->getParam("end_date", null);
        $type = $this->getRequest()->getParam(
            "type",
            \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_OPEN_LISTING_ALL_DATA
        );
        $requestMarketplaceIds = $this->getRequest()->getParam("marketplace_id", []);

        /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
        $account = $this->account->getById($accountId);

        /** @var array $marketplaceIds */
        $marketplaceIds = $account->getMarketplaceIds();

        if (!empty($requestMarketplaceIds) && is_array($requestMarketplaceIds)) {
            $marketplaceIds = array_intersect($marketplaceIds, $requestMarketplaceIds);
        }

        $status = false;
        foreach ($marketplaceIds as $marketplaceId) {
            $specifics = [
                'ids' => ['*'],
                'account_id' => $account->getId(),
                'marketplace' => $marketplaceId,
                'profile_id' => 0,
                'store_id' => $account->getStoreId(),
                'type' => $type,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];

            /** @var \Ced\Amazon\Api\Data\Queue\DataInterface $queueData */
            $queueData = $this->queueDataFactory->create();
            $queueData->setOperationType(\Amazon\Sdk\Base::OPERATION_TYPE_REQUEST);
            $queueData->setAccountId($account->getId());
            $queueData->setMarketplace($marketplaceId);
            $queueData->setSpecifics($specifics);
            $queueData->setType($type);
            $status = $this->queue->push($queueData);
        }

        if ($status != false) {
            $this->messageManager->addSuccessMessage(
                'Report request queued successfully. Please check the queue records.'
            );
        } else {
            $this->messageManager->addErrorMessage('Report request generation failed.');
        }

        return $this->_redirect('*/report');
    }
}
