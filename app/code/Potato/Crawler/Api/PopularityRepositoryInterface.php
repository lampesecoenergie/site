<?php

namespace Potato\Crawler\Api;

/**
 * @api
 */
interface PopularityRepositoryInterface
{
    /**
     * Create Popularity service
     *
     * @param \Potato\Crawler\Api\Data\PopularityInterface $popularity
     * @return \Potato\Crawler\Api\Data\PopularityInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(Data\PopularityInterface $popularity);

    /**
     * Get info about Popularity by popularity id
     *
     * @param int $popularityId
     * @return \Potato\Crawler\Api\Data\PopularityInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($popularityId);

    /**
     * Get info about Popularity by popularity url
     *
     * @param string $url
     * @return \Potato\Crawler\Api\Data\PopularityInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByUrl($url);

    /**
     * Delete popularity
     *
     * @param \Potato\Crawler\Api\Data\PopularityInterface $popularity
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(Data\PopularityInterface $popularity);
    
    /**
     * Create new empty Popularity interface
     *
     * @return \Potato\Crawler\Api\Data\PopularityInterface
     */
    public function create();
}