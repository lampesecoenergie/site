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

namespace Ced\Amazon\Controller\Adminhtml\Product\Feed;

use Ced\Amazon\Helper\Product;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;

/**
 * Class Preview
 * @package Ced\Amazon\Controller\Adminhtml\Product\Feed
 */
class Preview extends Action
{
    /**
     * @var Product
     */
    public $product;

    /** @var RawFactory  */
    public $resultFactory;

    public function __construct(
        Context $context,
        RawFactory $resultFactory,
        Product $product
    ) {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
        $this->product = $product;
    }

    public function execute()
    {
        $products = [];
        $sku = $this->getRequest()->getParam('sku');
        $id = $this->getRequest()->getParam('id');
        $xml = "";

        if (!empty($sku) && !empty($id)) {
            $ids = [$id];
            $profileIds = $this->product->profile->getProfileIdsByProductIds($ids);
            if (!empty($profileIds)) {
                /** @var \Magento\Framework\Api\SearchCriteriaInterface $search */
                $search = $this->product->search->setData(
                    'filter_groups',
                    [
                        [
                            'filters' => [
                                [
                                    'field' => \Ced\Amazon\Model\Profile::COLUMN_ID,
                                    'value' => $profileIds,
                                    'condition_type' => 'in'
                                ],
                                [
                                    'field' => \Ced\Amazon\Model\Profile::COLUMN_STATUS,
                                    'value' => \Ced\Amazon\Model\Source\Profile\Status::ENABLED,
                                    'condition_type' => 'eq'
                                ]
                            ]
                        ]
                    ]
                );

                /** @var \Ced\Amazon\Api\Data\ProfileSearchResultsInterface $profiles */
                $profiles = $this->product->profile->getList($search);

                /** @var \Ced\Amazon\Api\Data\AccountSearchResultsInterface $accounts */
                $accounts = $profiles->getAccounts();

                /** @var array $stores */
                $stores = $profiles->getProfileByStoreIdWise();

                /** @var \Ced\Amazon\Api\Data\AccountInterface $account */
                foreach ($accounts->getItems() as $accountId => $account) {
                    foreach ($stores as $storeId => $profiles) {
                        $envelope = null;
                        /** @var \Ced\Amazon\Api\Data\ProfileInterface $profile */
                        foreach ($profiles as $profileId => $profile) {
                            $productIds = $this->product->profile
                                    ->getAssociatedProductIds($profileId, $storeId, $ids);
                            $specifics = [
                                    'ids' => $productIds,
                                    'account_id' => $accountId,
                                    'marketplace' => $profile->getMarketplace(),
                                    'profile_id' => $profileId,
                                    'store_id' => $storeId,
                                    'type' => \Amazon\Sdk\Api\Feed::PRODUCT,
                                ];

                            if (!empty($productIds)) {
                                $envelope = $this->product->prepare($specifics, $envelope, "Update");
                                $p = $envelope->getData('xml');
                                if (is_string($p)) {
                                    $xml .= $p;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this->resultFactory
            ->create()
            ->setHeader('Content-type', 'text/xml')
            ->setContents($xml);
    }
}
