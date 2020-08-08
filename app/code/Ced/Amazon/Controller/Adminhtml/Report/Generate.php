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
 * Class Generate
 * @package Ced\Amazon\Controller\Adminhtml\Report
 */
class Generate extends Action
{
    /** @var \Ced\Amazon\Api\AccountRepositoryInterface  */
    public $account;

    /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory  */
    public $search;

    /** @var \Magento\Framework\Api\FilterBuilder  */
    public $filter;

    /** @var \Ced\Amazon\Api\Data\Queue\DataInterfaceFactory  */
    public $queueDataFactory;

    /**
     * @var \Ced\Amazon\Api\QueueRepositoryInterface
     */
    public $queue;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory $search,
        \Magento\Framework\Api\FilterBuilder $filter,
        \Ced\Amazon\Api\AccountRepositoryInterface $account,
        \Ced\Amazon\Api\QueueRepositoryInterface $queueRepository,
        \Ced\Amazon\Api\Data\Queue\DataInterfaceFactory $queueDataFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->search = $search;
        $this->account = $account;
        $this->queue = $queueRepository;
        $this->queueDataFactory = $queueDataFactory;
    }

    public function execute()
    {
        $status = false;

        /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder $criteria */
        $criteria = $this->search->create();
        $active = $this->filter->create()
            ->setField(\Ced\Amazon\Model\Account::COLUMN_ACTIVE)
            ->setConditionType('eq')
            ->setValue(\Ced\Amazon\Model\Source\Account\Active::ACTIVE);
        $criteria->addFilter($active);

        $status = $this->filter->create()
            ->setField(\Ced\Amazon\Model\Account::COLUMN_STATUS)
            ->setConditionType('eq')
            ->setValue(\Ced\Amazon\Model\Source\Account\Status::VALID);
        $criteria->addFilter($status);

        $accounts = $this->account->getList($criteria->create());
        /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
        foreach ($accounts->getItems() as $account) {
            $marketplaceIds = $account->getMarketplaceIds();
            foreach ($marketplaceIds as $marketplaceId) {
                $specifics = [
                    'ids' => ['*'],
                    'account_id' => $account->getId(),
                    'marketplace' => $marketplaceId,
                    'profile_id' => 0,
                    'store_id' => $account->getStoreId(),
                    'type' => \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_OPEN_LISTING_ALL_DATA,
                ];
                /** @var \Ced\Amazon\Api\Data\Queue\DataInterface $queueData */
                $queueData = $this->queueDataFactory->create();
                $queueData->setOperationType(\Amazon\Sdk\Base::OPERATION_TYPE_REQUEST);
                $queueData->setAccountId($account->getId());
                $queueData->setMarketplace($marketplaceId);
                $queueData->setSpecifics($specifics);
                $queueData->setType(
                    \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_OPEN_LISTING_ALL_DATA
                );
                $status = $this->queue->push($queueData);
            }
        }

        if ($status != false) {
            $this->messageManager->addSuccessMessage('Report generated successfully');
        } else {
            $this->messageManager->addErrorMessage('Report generation failed.');
        }

        return $this->_redirect('*/report');
    }
}
