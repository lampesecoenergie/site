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
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model;

use Magento\Framework\Cache\FrontendInterface;

class Cache
{
    /**
     * Cache key identifier
     */
    const CACHE_KEY_IDENTIFIER = 'ced_amazon_';

    const PROFILE_CACHE_KEY = 'profile_';

    const PROFILE_PRODUCT_CACHE_KEY = 'profile_product_';

    const CRON_STATUS_KEY = 'cron_status';

    /**
     * @var FrontendInterface
     */
    public $cache;

    /**
     * Cache constructor.
     * @param FrontendInterface $cache
     */
    public function __construct(
        FrontendInterface $cache
    ) {
        $this->cache = $cache;
    }

    /**
     * Get values
     * @param $key
     * @return mixed|null
     */
    public function getValue($key)
    {
        $cacheKey = self::CACHE_KEY_IDENTIFIER . $key;
        $value = $this->cache->load($cacheKey);
        return $value === false ? null : json_decode($value, true);
    }

    /**
     * Set Values
     * @param $key
     * @param $value
     * @param array $tags
     * @param null $lifeTime
     * @throws \Exception
     */
    public function setValue($key, $value, array $tags = [], $lifeTime = null)
    {
        if ($value === null) {
            $value = [];
        }

        if ($lifeTime === null || (int)$lifeTime <= 0) {
            $lifeTime = 60 * 60 * 24 * 365 * 5;
        }

        $cacheKey = self::CACHE_KEY_IDENTIFIER . $key;

        $preparedTags = [self::CACHE_KEY_IDENTIFIER . '_main'];
        foreach ($tags as $tag) {
            $preparedTags[] = self::CACHE_KEY_IDENTIFIER . '_' . $tag;
        }

        $this->cache->save(json_encode($value), $cacheKey, $preparedTags, (int)$lifeTime);
    }

    public function removeValue($key)
    {
        $cacheKey = self::CACHE_KEY_IDENTIFIER . $key;
        $this->cache->remove($cacheKey);
    }

    public function removeAllValues()
    {
        $this->removeTagValues('main');
    }

    public function removeTagValues($tag)
    {
        $tags = [self::CACHE_KEY_IDENTIFIER . '_' . $tag];
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_ALL, $tags);
    }
}
