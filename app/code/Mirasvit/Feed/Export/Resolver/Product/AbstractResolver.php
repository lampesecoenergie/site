<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Export\Resolver\Product;

use Mirasvit\Feed\Model\Feed;

abstract class AbstractResolver
{
    /**
     * @var Feed
     */
    private $feed;

    /**
     * @param Feed $feed
     * @return $this;
     */
    public function setFeed($feed)
    {
        $this->feed = $feed;

        return $this;
    }

    /**
     * @return Feed
     */
    public function getFeed()
    {
        return $this->feed;
    }

    /**
     * @param object $object
     * @param string $key
     * @return string
     */
    public function resolve($object, $key)
    {
        $exploded = explode(':', $key);

        $method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $exploded[0])));
        $args = [];
        for ($i = 1; $i < count($exploded); $i++) {
            $args[] = $exploded[$i];
        }

        if (method_exists($this, 'prepareObject')) {
            $object = $this->{'prepareObject'}($object);
        }

        if (method_exists($this, $method)) {
            return $this->{$method}($object, $args);
        }

        if (method_exists($this, 'getData')) {
            return $this->getData($object, $key);
        }

        return false;
    }
}