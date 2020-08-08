<?php

namespace Potato\Crawler\Api;

/**
 * @api
 */
interface QueueManagerInterface
{
    /**
     * @param string $url
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param int $priority
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function addUrl($url, $store, $priority);
}