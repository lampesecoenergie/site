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

namespace Ced\Amazon\Api;

/**
 * Interface FeedRepositoryInterface
 * @package Ced\Amazon\Api
 * @api
 */
interface FeedRepositoryInterface extends \Ced\Integrator\Api\FeedRepositoryInterface
{
    /**
     * Send Feed to Marketplace
     * @param \Amazon\Sdk\Envelope|null $envelope
     * @param array $specifics
     * @return array
     */
    public function send(\Amazon\Sdk\Envelope $envelope = null, $specifics = []);

    /**
     * Sync feed result
     * @param $id
     * @param \Ced\Amazon\Api\Data\FeedInterface|null $feed
     * @return boolean
     */
    public function sync($id, $feed = null);

    /**
     * Resend feed
     * @param $id
     * @return boolean
     */
    public function resend($id);

    /**
     * @param $id
     * @return \Ced\Amazon\Api\Data\FeedInterface
     */
    public function getById($id);

    /**
     * @param $feedId
     * @return \Ced\Amazon\Api\Data\FeedInterface
     */
    public function getByFeedId($feedId);

    /**
     * Get Api Results By Feed Id
     * @param string $feedId
     * @param string $accountId
     * @return bool|string
     */
    public function getResultByFeedId($feedId, $accountId);

    /**
     * Get all Data
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ced\Amazon\Api\Data\FeedSearchResultsInterface
     * @throws \Exception
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Save
     * @param \Ced\Amazon\Api\Data\FeedInterface $feed
     * @return int
     * @throws \Exception
     */
    public function save(\Ced\Amazon\Api\Data\FeedInterface $feed);

    /**
     * Clear old records
     * @param null $created
     * @param null $collection
     * @return boolean
     * @throws \Exception
     */
    public function clearRecords($created = null, $collection = null);
}
