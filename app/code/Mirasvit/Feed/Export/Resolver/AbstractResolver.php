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



namespace Mirasvit\Feed\Export\Resolver;

use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Feed\Export\Context;

abstract class AbstractResolver
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Export Context
     *
     * @var \Mirasvit\Feed\Export\Context
     */
    protected $context;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
        $this->context = $context;
        $this->storeManager = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $this->filesystem = $this->objectManager->get('Magento\Framework\Filesystem');
    }

    /**
     * List of allowed attributes
     *
     * @return array
     */
    abstract public function getAttributes();

    /**
     * General resolver
     *
     * @param object $object
     * @param string $key
     * @param []     $args
     *
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function resolve($object, $key, $args = [])
    {
        $this->context->setCurrentObject($object);

        /** @var \Mirasvit\Feed\Export\Resolver\Pool $pool */
        $pool = $this->objectManager->get('Mirasvit\Feed\Export\Resolver\Pool');

        $resolver = $pool->findResolver($object);

        if ($resolver && !($resolver instanceof $this)) {
            return $resolver->resolve($object, $key, $args);
        } elseif ($resolver instanceof $this && !$key) {
            return $resolver->toString($object);
        } else {
            $exploded = explode(':', $key);

            $method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $exploded[0])));
            for ($i = 1; $i < count($exploded); $i++) {
                $args[] = $exploded[$i];
            }

            if (method_exists($this, 'prepareObject')) {
                $object = $this->{'prepareObject'}($object);
            }

            $result = false;
            if (method_exists($this, $method)) {
                $result = $this->{$method}($object, $args);
            } elseif (method_exists($this, 'getData')) {
                $result = $this->getData($object, $key);
            } elseif (method_exists($object, $method)) {
                $result = $object->{$method}();
            } elseif (method_exists($object, 'getData')) {
                $result = $object->getData($exploded[0]);
            }

            return $result;
        }

        return false;
    }

    /**
     * Return string value of object
     *
     * @param object|array|string $value
     * @param string $key
     * @return string
     */
    public function toString($value, $key = null)
    {
        if (!$key && is_object($value)) {
            return get_class($value);
        }

        if (is_array($value)) {
            # is multi-dimension array
            if (isset($value[0]) && is_array($value[0])) {
                return json_encode($value);
            } else {
                try {
                    return implode(', ', $value);
                } catch (\Exception $e) {
                    return json_encode($value);
                }
            }
        }

        return $value;
    }

    /**
     * Feed model
     *
     * @return \Mirasvit\Feed\Model\Feed
     */
    public function getFeed()
    {
        return $this->context->getFeed();
    }
}
