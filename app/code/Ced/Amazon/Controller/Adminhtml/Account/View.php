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

namespace Ced\Amazon\Controller\Adminhtml\Account;

/**
 * Class View
 * @package Ced\Amazon\Controller\Adminhtml\Account
 */
class View extends \Magento\Backend\App\Action
{
    /** @var \Ced\Amazon\Api\AccountRepositoryInterface  */
    public $account;

    /** @var \Ced\Amazon\Helper\Logger  */
    public $logger;

    public $marketplace;

    /**
     * View constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Ced\Amazon\Model\AccountFactory $account
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ced\Amazon\Helper\Logger $logger,
        \Ced\Amazon\Api\AccountRepositoryInterface $accountRepository,
        \Amazon\Sdk\Marketplace $marketplace
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->account = $accountRepository;
        $this->marketplace = $marketplace;
    }

    public function execute()
    {
        $data = [
            'id' => 0,
            'marketplace' => [],
            'store_id' => 0,
        ];

        $id = $this->getRequest()->getParam('id');

        try {
            /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
            $account = $this->account->getById($id);
            $marketplace = [];
            $marketplaceIds = $account->getMarketplaceIds();
            if (!empty($marketplaceIds)) {
                foreach ($marketplaceIds as $id) {
                    $marketplace[] = $this->marketplace->get($id);
                }
            }

            $data = [
                'id' => $account->getId(),
                'marketplaceIds' => $marketplaceIds,
                'marketplace' => $marketplace,
                'store_id' => $account->getStoreId(),
            ];
        } catch (\Exception $e) {
            $this->logger->error(
                'Invalid account accessed.',
                ['id' => $id, 'path' => __METHOD__]
            );
        }

        /** @var \Magento\Framework\Controller\Result\Json $response */
        $response = $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $response->setData($data);

        return $response;
    }
}
