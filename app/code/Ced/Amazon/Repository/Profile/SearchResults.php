<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Repository\Profile;

class SearchResults extends \Ced\Integrator\Repository\SearchResults implements
    \Ced\Amazon\Api\Data\ProfileSearchResultsInterface
{
    /** @var \Ced\Amazon\Api\AccountRepositoryInterface  */
    public $account;

    public function __construct(
        \Ced\Amazon\Api\AccountRepositoryInterface $accountRepository,
        array $data = []
    ) {
        parent::__construct($data);
        $this->account = $accountRepository;
    }

    /**
     * Accounts for the ids available in the search result
     * @return \Ced\Amazon\Api\Data\AccountSearchResultsInterface
     */
    public function getAccounts()
    {
        $accountIds = [];
        /** @var \Ced\Amazon\Api\Data\ProfileInterface $item */
        foreach ($this->getItems() as $item) {
            $accountIds[] = $item->getAccountId();
        }

        return $this->account->getByIds($accountIds);
    }

    /**
     * Get profiles arranged by store ids
     * @return array
     */
    public function getProfileByStoreIdWise()
    {
        $result = [];
        /** @var \Ced\Amazon\Api\Data\ProfileInterface $item */
        foreach ($this->getItems() as $item) {
            $result[$item->getStoreId()][$item->getId()] = $item;
        }

        return $result;
    }
}
